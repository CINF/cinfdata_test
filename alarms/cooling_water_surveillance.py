#!/bin/env python

""" This script monitors the cooling water status for the CINF experimental
hall and sends out email alerts if there is something wrong with the cooling
water.

To change who receives the emails, edit the 'users' file in the script
directory. To find the full path for the file, search for EDIT_POINT

To change the cooling water checking conditions edit the 'cooling_water_check'
function.
"""

##############################################################################
# Settings                                                                   #
##############################################################################

# Table to fetch the data from, list of dictionaries
MEASUREMENTS = [
    {'name': 'Ambient',
     'table':'temperature_hall_microreactors'},
    #{'name': 'Containment turbo NG',
    # 'table':'temperature_microreactorNG_containmentturbo'},
    #{'name': 'Chamber turbo microreactor',
    # 'table':'temperature_microreactor_chamberturbo'},
    #{'name': 'Buffer turbo microreactor',
    # 'table':'temperature_microreactor_bufferturbo'},
#    {'name': 'Microreactor NG',
#     'table':'dateplots_microreactorNG',
#     'type_list': ['T_bufferturbo', 'T_chamberturbo']},
    {'name': 'STM312 Big Turbo',
     'table': 'temperature_stm312_big_turbo'},
    {'name': 'STM312 gas handling',
     'table': 'temperature_stm312_gas_handling'},
    {'name': 'STM312 sputter gun',
     'table': 'temperature_stm312_sputter_gun'}
]

# This is the values used for the comparisons
TOLERANCE_RAISE = 4
TOLERANCE_FALL = 3
# ABSOLUTE_MAX Temporarily set to 33 (normal 30)
# while the cooling water is being fixed
ABSOLUTE_MAX = 33
NUMBER_OF_ERRORS_TRIGGER = 3
CRONJOB_INTERVAL = 10  # min

# Files that contains the email adresses of the users and the testers
USERS = '/var/www/cinfdata/cooling_water_surveillance/users'
TESTERS = '/var/www/cinfdata/cooling_water_surveillance/testers'

# Settings for email account
USER_EMAIL = 'cinf_cooling_water_warning@servcinf.fysik.dtu.dk'
SMTP_SERVER = '127.0.0.1'
RECIPIENT_USERS = 'cinf_cooling_water_users@servcinf.fysik.dtu.dk'
RECIPIENT_TESTERS = 'cinf_cooling_water_testers@servcinf.fysik.dtu.dk'

##############################################################################
# Functions                                                                  #
##############################################################################
import MySQLdb
import time


def print_email(message='', subject='', warning=False):
    """ Dummy email method for testing """
    if warning:
        pass
    print '####################################'
    print 'SUBJECT: ' + subject
    print '####################################'
    print message
    print '####################################'


def send_email(message='', subject='', warning=False):
    """ This function sends an email from a local smtp server

    Parameters:
    message    A text string to put in the email body
    subject    A text string to put in the email subject
    warning    Boolean True if it is a warning
    """

    ### EDIT POINT START
    if warning:
        recipient = RECIPIENT_USERS
    else:
        recipient = RECIPIENT_TESTERS
    ### EDIT POINT END

    # Read email adresses of users or testers
    if warning:
        with open(USERS) as file_:
            email_adresses = file_.read().rstrip('\n').split('\n')
    else:
        with open(TESTERS) as file_:
            email_adresses = file_.read().rstrip('\n').split('\n')

    # Imports
    import smtplib
    from email.mime.text import MIMEText

    # Create a text/plain message
    msg = MIMEText(message)

    # Header info
    msg['Subject'] = subject
    msg['From'] = USER_EMAIL
    msg['To'] = recipient

    # Send the message via our own SMTP server
    server = smtplib.SMTP(SMTP_SERVER)
    server.sendmail(USER_EMAIL, email_adresses, msg.as_string())
    server.quit()


def cooling_water_check(tolerance_raise, tolerance_fall, absolute_max):
    """ This function gets the surveillance data from the database,
    performs checks on the data and marks the data with the result of the
    checks
    """
    database = MySQLdb.connect(user='cinf_reader', passwd='cinf_reader',
                               db='cinfdata')
    cursor = database.cursor()

    # Form the list of measurements from MEASUREMENTS, so that each type, if
    # present each get its own entry
    measurements = []
    for measurement in MEASUREMENTS:
        if 'type_list' in measurement:
            for type_ in measurement['type_list']:
                temp_meas = measurement.copy()
                temp_meas['type'] = type_
                temp_meas['name'] += ' ({0})'.format(type_)
                measurements.append(temp_meas)
        else:
            measurements.append(measurement)

    # Fetch the data and put the values for 'now' and '1h_ago' in meas
    for meas in measurements:
        data = get_data(meas, cursor)
        meas.update(data)

    alert_count = 0
    for meas in measurements:
        # IF one of the queries failed to get data, possibly due to broken
        # measurement, we fill in dummy data
        # ELIF Not ambient and difference larger than tolerance
        if meas['now'][1] is None or meas['1h_ago'][1] is None:
            meas['alert'] = None
            meas['now'] = meas['1h_ago'] = (0, 0)
        elif (meas['name'] != 'Ambient' and
              (meas['now'][1] - meas['1h_ago'][1] > tolerance_raise)):
            meas['alert'] = True
            alert_count += 1
        elif (meas['name'] != 'Ambient' and
              (meas['1h_ago'][1] - meas['now'][1] > tolerance_fall)):
            meas['alert'] = True
            alert_count += 1
        elif (meas['name'] != 'Ambient' and
              (meas['now'][1] > absolute_max)):
            meas['alert'] = True
            alert_count += 1
        else:
            meas['alert'] = False

    return alert_count, measurements


def get_data(measurement, cursor):
    """ For the queries and get the data """
    if 'type' in measurement:
        query_template = (
            'SELECT unix_timestamp(time), value FROM {{0}} where '
            'value between -1 and 1300 and time between "{{1}}" and '
            '"{{2}}" and type="{0}" order by time desc limit 1;'
            ).format(measurement['type'])
    else:
        query_template = (
            'SELECT unix_timestamp(time), temperature FROM {0} where '
            'temperature between -1 and 1300 and time between "{1}" and '
            '"{2}" order by time desc limit 1;'
            )

    return_values = {}
    for timename, hour in zip(['now', '1h_ago'], [1, 2]):
        query = query_template.format(
            measurement['table'],
            time.strftime('%Y-%m-%d %H:%M',
                          time.localtime(time.time() - 3600 * hour)),
            time.strftime('%Y-%m-%d %H:%M',
                          time.localtime(time.time() - 3600 * (hour - 1)))
            )

        cursor.execute(query)
        data = cursor.fetchall()
        # If there is no data, fill in Nones
        return_values[timename] = data[0] if len(data) > 0 else (None, None)
    return return_values


def format_measurements(measurements):
    """ Format the measurements into nice text """
    # Error status to text
    errors = {False: '..........ok', True: '..........NOT GOOD',
              None: '..........NO MEASUREMENT'}
    message = ''

    # Generate output
    for meas in measurements:
        # One big fat string formatting
        message += '=== {0} ===\n{1:.2f}C at {2}\n{3:.2f}C at {4}\n{5}\n\n'.\
            format(meas['name'],
                   round(meas['1h_ago'][1], 2),
                   time.strftime('%Y-%m-%d %H:%M',
                                 time.localtime(meas['1h_ago'][0])),
                   round(meas['now'][1], 2),
                   time.strftime('%Y-%m-%d %H:%M',
                                 time.localtime(meas['now'][0])),
                   errors[meas['alert']])

    return message


def email_body_templates(email_type):
    """ Contains and returns the email body templated """
    if email_type == 'alert':
        return (
            'The survelliance of the COOLING WATER has TRIGGERED AN '
            'ALERT, because {0} temperature measurements of turbo '
            'pumps are out of bounds.\n\n'
            'The bounds are:\n'
            'Temperature has risen more than: {1}C with the last hour\n'
            'Temperature has fallen more than: {2}C within the last hour\n'
            'Temperature temperature has crossed the hard absolute limit '
            'of: {3}C\n'
            '\n'
            'The measurements at the time of the alert are:\n'
            '{4}\n'
            'Please CONFIRM that this does indeed indicate that the '
            'cooling '
            'water temperature is on the rise by looking at the graphs '
            'on these three pages:\n'
            'https://cinfdata.fysik.dtu.dk/microreactorNG/'
            'read_dateplot.php?type=temperature_turbos\n'
            'https://cinfdata.fysik.dtu.dk/microreactor/'
            'read_dateplot.php?type=temperature_turbos\n'
            'https://cinfdata.fysik.dtu.dk/tof/'
            'read_dateplot.php?type=temperature_tof_turbopump\n'
            'https://cinfdata.fysik.dtu.dk/stm312/'
            'read_dateplot.php?type=turbo_temperatures\n'
            '\n'
            'If you decide to go to CINF outside of normal office '
            'hours to correct the problem, PLEASE inform everybody '
            'else by sending an email to the "B312 and more" group '
            'on campusnet.')
    elif email_type == 'status':
        return ('I\'m the friendly cooling water surveillance script '
                'checking in to let you know that I\'m running just '
                'fine.\n'
                '\n'
                'The values of the temperature measurements at last '
                'check was:\n'
                '{0}'
                '\n'
                'And now it\'s time to go to lunch!')
    else:
        return None


##############################################################################
# Main code                                                                  #
##############################################################################


def main():
    """ Main cooling water surveillance execution """
    # The big try-except sends an email if the script generates an exception,
    # provided it is not in the send email part..!
    try:
        # Temporary log
        with open('/tmp/cooling_water_surveillance_log', 'a') as file_:
            file_.write(time.strftime('%Y-%m-%d %H:%M') + '\n')

        alert_count, measurements = cooling_water_check(
            TOLERANCE_RAISE, TOLERANCE_FALL, ABSOLUTE_MAX
            )

        # Danger, Will Robinson
        # To many measurements have generated alerts
        if alert_count >= NUMBER_OF_ERRORS_TRIGGER:
            subject = 'CINF COOLING WATER ALERT'
            message = email_body_templates('alert').format(
                alert_count, TOLERANCE_RAISE, TOLERANCE_FALL, ABSOLUTE_MAX,
                format_measurements(measurements)
                )
            send_email(message, subject, warning=True)
        else:
            # Everything is good
            if (time.localtime().tm_hour == 12 and
                time.localtime().tm_min < CRONJOB_INTERVAL):
                # It's time to go to lunch
                subject = 'Cooling water surveillance check-in'
                message = email_body_templates('status').format(
                    format_measurements(measurements)
                    )
                send_email(message, subject, warning=False)
    except:  # pylint: disable=W0702
        import traceback
        message = ('The script generated the following exception:'
                   '\n\n{0}').format(traceback.format_exc())
        subject = 'Cooling water surveillance script generated an error'
        send_email(message, subject, warning=False)

if __name__ == '__main__':
    main()
