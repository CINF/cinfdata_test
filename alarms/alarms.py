#!/usr/bin/python
# pylint: disable=star-args

"""Script that checks the user defined alarms on logged values

The alarms are defined in the alarms table. Which among other things
contains a list of queries and a list of parameters. Each of the
quiries must return rows on the form (unix_timestamp, value). The
quiries must return exactly 1 row except if used for dqdt. The checks
that can be performed are the following:

Greater than: s0 > s1
Less than: s0 < s1

Where s are the substitution markers. They can either be:
 * q0, q1, ... which are query numbers
 * p0, p1, ... which are parameter numbers
 * dqdt0 which is the slope of the points in query 0

The syntax allows for 0 or 1 spaces between the parts, which means
that both "s0 > s1" and "s0>s1" is valid, but not "s0 >  s1".

The expression can be chained together with "and" or "or" as such:

s0 < s1 and s2 > s3

Examples of complete check could be:

q0 < p0
q0 < p0 and q1 > p1

"""


from __future__ import print_function
import re
import sys
import json
import time
import smtplib
from email.mime.text import MIMEText
from collections import defaultdict, namedtuple
import logging
from logging.handlers import RotatingFileHandler
import numpy as np
import MySQLdb

# Regular expression used to match the check, which are in the form:
# "q0 < p0" or "dqdt0 < p0"
CHECK_RE = re.compile('^ ?([pq][0-9]*|dqdt[0-9]+) ?([<>]) ?([pq][0-9]*|dqdt[0-9]+) ?$')
CHECK_AND_OR = re.compile('(and|or)')


# pylint: disable=too-many-arguments, too-many-locals
def get_logger(name, level='INFO', terminal_log=True, file_log=False,
               file_name=None, file_max_bytes=1048576, file_backup_count=3):
    """Copy from PyExpLabSys.common.utilities. See that module for details."""
    # Get the root logger and set the level
    log_level = getattr(logging, level.upper())
    root_logger = logging.getLogger('')
    root_logger.setLevel(log_level)

    handlers = []
    # Form the handler(s) and set the level
    if terminal_log:
        stream_handler = logging.StreamHandler()
        stream_handler.setLevel(log_level)
        handlers.append(stream_handler)

    # Create rotating file handler
    if file_log:
        if file_name is None:
            file_name = name + '.log'
        file_handler = RotatingFileHandler(file_name, maxBytes=file_max_bytes,
                                           backupCount=file_backup_count)
        file_handler.setLevel(log_level)
        handlers.append(file_handler)

    # Add formatters to the handlers and add the handlers to the root_logger
    formatter = logging.Formatter(
        '%(asctime)s:%(name)s: %(levelname)s: %(message)s')
    for handler in handlers:
        handler.setFormatter(formatter)
        root_logger.addHandler(handler)

    # Create a named logger and return it
    logger = logging.getLogger(name)
    return logger


_LOG = get_logger(__file__, level='debug', file_log=True, file_name='alarm_log')


class ErrorDuringCheck(Exception):
    """Exception for a bad check definition"""
    pass


# pylint: disable=too-few-public-methods
class CheckAlarms(object):
    """Class that runs the alarm checks"""

    def __init__(self, dbmodule=MySQLdb):
        _LOG.debug('__init__(dbmodule={0})'.format(dbmodule))
        _db = dbmodule.connect(host='servcinf', user='alarm',
                               passwd='alarm', db='cinfdata')
        self._alarm_cursor = _db.cursor()
        _db = dbmodule.connect(host='servcinf', user='cinf_reader',
                               passwd='cinf_reader', db='cinfdata')
        self._reader_cursor = _db.cursor()
        self._smtp_server_address = '127.0.0.1'

    def check_alarms(self):
        """Checks the alarms and sends out emails if necessary"""
        _LOG.debug("check_alarms()")
        alarms = self._get_alarms()
        for alarm in alarms:
            _LOG.debug('Check alarm: {0}, {1}'.format(alarm['id'], dict(alarm)))
            if 'error' in alarm:
                # Send email about error parsing the json
                body = (
                    'At least one error occurred while trying to parse the '\
                    'alarm. The error message(s) was:{0}\n\n'
                    'The entire (half parsed) alarm definition was:\n\n{1}'
                ).format(alarm['error'], dict(alarm))

                # When the recipients JSON is broken, we cannot send them an
                # email about it! Send it to Robert and Kenneth instead.
                if 'recipients' not in alarm:
                    alarm['recipients'] = ['pyexplabsys-error@fysik.dtu.dk']
                    subject = 'Error in parsing recipients alarm JSON'
                    body += '\n\nTHIS EMAIL WAS ONLY SENT TO YOU'
                else:
                    subject = 'Error in parsing alarm'

                _LOG.info('Error in alarm parsing. Alarm: {0}'.format(alarm))
                self._send_email(subject, body, alarm['recipients'])

                continue

            # Try and check a single alarm
            try:
                status, check_string = self._check_single_alarm(
                    alarm['quiries'],
                    alarm['parameters'],
                    alarm['check']
                )
            except ErrorDuringCheck as exp:
                subject = 'Error during check of alarm'
                body = 'An error was encountered during check of an alarm. '\
                       'The complete alarm definition is:\n{0}\n\nThe error '\
                       'was:\n{1}'.format(dict(alarm), exp.message)
                _LOG.debug('Error during check: {0}'.format(exp.message))
                self._send_email(subject, body, alarm['recipients'])
                continue

            # Warn if necessary
            if status:
                _LOG.debug("Raise alarm for check string {0}".format(check_string))
                self._raise_alarm(alarm, check_string)
            else:
                _LOG.debug("No alarm for chech string {0}".format(check_string))

    def _get_alarms(self):
        """Get the list of alarms to check"""
        _LOG.debug('_get_alarms()')
        fields = ('id', 'quiries_json', 'parameters_json', 'check',
                  'no_repeat_interval', 'message', 'recipients_json',
                  'subject')
        # Column names needs to be excaped with backticks, because
        # someone was stupid enough to pick one which is a reserved
        # word (check)
        fields_string =' ,'.join(['`{0}`'.format(field) for field in fields])
        query = 'SELECT {0} FROM alarm WHERE visible=1 AND active=1'.format(fields_string)
        self._alarm_cursor.execute(query)

        # Turns column names and rows into dict and decode json
        alarms = []
        for rows in self._alarm_cursor.fetchall():
            alarm = defaultdict(str)
            for name, value in zip(fields, rows):
                if name.endswith('_json'):
                    try:
                        parsed_value = json.loads(value)
                        if not isinstance(parsed_value, list):
                            alarm += '\n\nColumn {0} does not contain an JSON '\
                                     'encoded list'.format(name)
                        alarm[name.replace('_json', '')] = parsed_value

                    except ValueError:
                        alarm['error'] += \
                            '\n\nCould not decode json string '\
                            '"{0}" in column {1}'.format(value, name)
                else:
                    alarm[name] = value

                if name == 'parameters_json':
                    for parameter in alarm['parameters']:
                        if not isinstance(parameter, float):
                            alarm['error'] += \
                                '\n\nParameters must be floats not {0}'.\
                                format(type(parameter).__name__)

            alarms.append(alarm)

        return alarms

    def _raise_alarm(self, alarm, check_string):
        """Raises an alarm, if not inhibited by no_repeat_interval"""
        _LOG.debug('_raise_alarm()')
        last_alarm_time = self._get_time_of_last_alarm(alarm['id'])
        # Alarm is not inhibited by no_repeat_interval
        if last_alarm_time is None or\
           time.time() - last_alarm_time > alarm['no_repeat_interval']:
            if last_alarm_time is None:
                _LOG.debug('No previous alarm within no_repeat_interval. '
                           'No previous alarm.')
            else:
                diff = time.time() - last_alarm_time
                _LOG.debug('No previous alarm within no_repeat_interval. Time '
                           'since last: {0:.1f}'.format(diff))

            query = "INSERT INTO alarm_log (alarm_id) VALUES (%s)"
            self._alarm_cursor.execute(query, (alarm['id']))

            subject = alarm['subject']
            if subject == '':
                subject = 'Surveillance alarm'
            body = '{message}\n\nAUTO-GENERATED:\nThe check was: {check}\n'\
                   'and the check string was: {0}'.format(check_string, **alarm)

            self._send_email(subject, body, alarm['recipients'])
        else:
            _LOG.debug('Previous alarm {0:.1f} seconds ago. Do not send an '
                       'email this time'.format(time.time() - last_alarm_time))

    def _get_time_of_last_alarm(self, alarm_id):
        """Returns the time of last alarm for alarm_id"""
        _LOG.debug('_get_time_of_last_alarm(alarm_id={0})'.format(alarm_id))
        query = 'select unix_timestamp(time) from alarm_log where alarm_id = '\
                '%s order by time desc limit 1;'
        self._alarm_cursor.execute(query, (alarm_id))
        result = self._alarm_cursor.fetchall()
        if len(result) == 0:
            return None
        else:
            # First column of first (and only) result
            return result[0][0]

    def _check_single_alarm(self, quiries, parameters, check):
        """Checks a single alarm

        Args:
            quiries (list): List of queries
            parameters (list): List of parameters
            check (str): The check to perform
        Returns:
            tuple: (alarm_boolean, value) where alarm_boolean is True if the
                alarm is triggered
        """
        _LOG.debug('_check_single_alarm(quiries={0}, parameters={1}, '
                   'check="{2}")'.format(quiries, parameters, check))

        # Parse check, first split over "and" and "or"
        and_or_tokens = CHECK_AND_OR.split(check)
        tokens = []
        for token in and_or_tokens:
            if token in ['and', 'or']:
                tokens.append(token)
                continue
            print("###", token)
            match = CHECK_RE.match(token)
            if match:
                # Append dict for the matches
                check_part = dict(zip(
                        ("sub0", "comp", "sub1"),
                        match.groups()
                        ))
                tokens.append(self._replace_substitutions(check_part, quiries,
                                                          parameters))
            else:
                message = 'Bad format for the check: "{0}"'.format(check)
                raise ErrorDuringCheck(message)


        res = self._eval_check(tokens[0])
        _LOG.debug('=Eval set: {0}'.format(res))
        CHECK = '{sub0} {comp} {sub1}'
        check_string = CHECK.format(**tokens[0])
        next = None
        for token in tokens[1:]:
            if token in ['and', 'or']:
                next = token
                check_string += ' {0} '.format(token)
                continue

            if not isinstance(token, dict):
                message = 'Bad token. Expected check part dict'
                raise ErrorDuringCheck(message)

            check_string += CHECK.format(**token)
            current = self._eval_check(token)
            if next == 'and':
                res = res and current
                _LOG.debug('=Eval added: and {0}'.format(current))
            elif next == 'or':
                res = res or current
                _LOG.debug('=Eval added: or {0}'.format(current))
            else:
                message = 'Bad format. Expected and/or'
                raise ErrorDuringCheck(message)

        return res, check_string

    def _eval_check(self, check_part):
        """Check a sigle part of the expression"""
        _LOG.debug('_eval_check(check_part={0}'.format(check_part))
        if check_part['comp'] == '<':
            if check_part['sub0'] < check_part['sub1']:
                return True
        elif check_part['comp'] == '>':
            if check_part['sub0'] > check_part['sub1']:
                return True
        return False

    def _replace_substitutions(self, check_part, quiries, parameters):
        """Replace substitutions in a check part

        Args:
            check_part (dict): Check part dict
            quiries (list): List of quiries
            parameters (list): List of parameters
        """
        _LOG.debug('_replace_substitutions(check_part={0}, quiries={1}, '
                   'parameters={2})'.format(check_part, quiries, parameters))
        for key in ['sub0', 'sub1']:
            sub = check_part[key]
            if sub.startswith('q'):  # Query, e.g: q0
                query_number = int(sub[1:])
                try:
                    query = quiries[query_number]
                except IndexError:
                    message = 'Bad query index {0}, expected number below {1}'\
                              .format(query_number, len(quiries))
                    raise ErrorDuringCheck(message)
                # Value is second item
                check_part[key] = self._query(query)[1]
            elif sub.startswith('p'):  # Parameter, e.g: p1
                parameter_number = int(sub[1:])
                try:
                    check_part[key] = parameters[parameter_number]
                except IndexError:
                    message = 'Bad parameter index {0}, expected number below '\
                              '{1}'.format(parameter_number, len(quiries))
                    raise ErrorDuringCheck(message)
            elif sub.startswith('dqdt'):
                query_number = int(sub[4:])
                try:
                    query = quiries[query_number]
                except IndexError:
                    message = 'Bad query index {0}, expected number below {1}'\
                              .format(query_number, len(quiries))
                    raise ErrorDuringCheck(message)
                rows = self._query(query, single=False)
                if len(rows) < 2:
                    message = 'The slope query returned less than 2 results'
                    raise ErrorDuringCheck(message)
                x, y = zip(*rows)
                try:
                    # polyfit returns coeffs. from order of decreasing power
                    # so first item in 1st degree fit is the slope
                    check_part[key] = np.polyfit(x, y, 1)[0]
                except Exception as exp:
                    message = 'An error happened during the linear regression.'\
                        ' The error message was: {0}'.format(exp.message)
                    raise ErrorDuringCheck(message)
            else:
                message = 'Unknown substitution description: "{0}"'.format(sub)
                raise ErrorDuringCheck(message)
            _LOG.debug('Substitute "{0}" with "{1}"'
                       ''.format(sub, check_part[key]))


        return check_part

    def _query(self, query, single=True):
        """Fetches values for a query"""
        _LOG.debug('_query("{0}", single={1})'.format(query, single))
        self._reader_cursor.execute(query)
        rows = self._reader_cursor.fetchall()
        # Check that there are rows in the result
        if len(rows) == 0:
            message = 'The query "{0}" produced 0 rows of results'.format(query)
            raise ErrorDuringCheck(message)

        # Check that results are on the (unixtime, value) form
        for row in rows:
            if len(row) != 2:
                message = 'The query "{0}" produced results, where the number '\
                          'of columns is not 2. The results must always be '\
                          'on the (unixtime, value) form'.format(query)
                raise ErrorDuringCheck(message)

        # If only a single row is expected
        if single:
            if len(rows) > 1:
                message = 'The query "{0}" produced more than 1 row of '\
                          'results, which is the expected'.format(query)
                raise ErrorDuringCheck(message)
            return rows[0]
        else:
            return rows

    def _send_email(self, subject, body, recipients):
        """Sends an email with the specified content"""
        _LOG.debug('_send_email(subject="{0}", body="{1}...", recipients={2})'
                   ''.format(subject, body.split('\n')[0], recipients))

        msg = MIMEText(body)
        # Header info
        msg['Subject'] = subject
        msg['From'] = 'no-reply@fysik.dtu.dk'
        msg['To'] = ', '.join(recipients)
        msg['Reply-To'] = ', '.join(recipients)

        # Send the message via our own SMTP server
        attempts = 0
        while attempts < 3:
            try:
                smtp_server = smtplib.SMTP(self._smtp_server_address)
                smtp_server.sendmail('no-reply@fysik.dtu.dk', recipients, msg.as_string())
                smtp_server.quit()
                break
            except smtplib.SMTPException:
                attempts += 1
                time.sleep(10)
        else:
            raise IOError('Unable to send email')


def main():
    """Main method"""
    _LOG.info('Script started')
    while True:
        check_alarms = CheckAlarms()
        try:
            while True:
                check_alarms.check_alarms()
                time.sleep(60)
        except Exception as exp:
            _LOG.exception("An error occoured during alarm script")
            check_alarms._send_email("Alarm script generated error", str(exp.message), ['knielsen@fysik.dtu.dk'])

if __name__ == '__main__':
    main()
