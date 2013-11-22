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
# Functions                                                                  #
##############################################################################

def send_email(message='', subject='', warning=False):
    """ This function sends an email from a local smtp server

    Parameters:
    message    A text string to put in the email body
    subject    A text string to put in the email subject
    warning    Boolean True if it is a warning
    """

    ### EDIT POINT START
    # Files that contains the email adresses of the users and the testers
    users = '/var/www/cinfdata/cooling_water_surveillance/users'
    testers = '/var/www/cinfdata/cooling_water_surveillance/testers'

    # Settings for email account
    user_email = 'cinf_cooling_water_warning@servcinf.fysik.dtu.dk'
    smtp_server = '127.0.0.1'
    if warning:
        recipient = 'cinf_cooling_water_users@servcinf.fysik.dtu.dk'
    else:
        recipient = 'cinf_cooling_water_testers@servcinf.fysik.dtu.dk'
    ### EDIT POINT END

    # Read email adresses of users or testers
    if warning:
        with open(users) as f:
            email_adresses = f.read().rstrip('\n').split('\n')
    else:
        with open(testers) as f:
            email_adresses = f.read().rstrip('\n').split('\n')

    # Imports
    import smtplib
    from email.mime.text import MIMEText

    # Create a text/plain message
    msg = MIMEText(message)

    # Header info
    msg['Subject'] = subject
    msg['From'] = user_email
    msg['To'] = recipient

    # Send the message via our own SMTP server
    server = smtplib.SMTP(smtp_server)
    server.sendmail(user_email, email_adresses, msg.as_string())
    server.quit()

def cooling_water_check(tolerance_raise, tolerance_fall, absolute_max):
    """ This function gets the surveillance data from the database,
    performs checks on the data and marks the data with the result of the
    checks.
    """

    import MySQLdb
    import time
    db = MySQLdb.connect(user='cinf_reader', passwd='cinf_reader', db='cinfdata')
    cursor = db.cursor()

    # 1 and 2 hours ago, used as bounds to the get the value from 1 h ago
    start = time.strftime('%Y-%m-%d %H:%M', time.localtime(time.time() - 7200))
    end = time.strftime('%Y-%m-%d %H:%M', time.localtime(time.time() - 3600))

    # Table to fetch the data from, list of dictionaries
    measurements = [{'name': 'Ambient',
                     'table':'temperature_hall_microreactors'},
                    #{'name': 'Chamber turbo NG',
                    # 'table':'temperature_microreactorNG_chamberturbo'},
                    #{'name': 'Buffer turbo NG',
                    # 'table':'temperature_microreactorNG_bufferturbo'},
                    {'name': 'Containment turbo NG',
                     'table':'temperature_microreactorNG_containmentturbo'},
                    {'name': 'Chamber turbo microreactor',
                     'table':'temperature_microreactor_chamberturbo'},
                    {'name': 'Buffer turbo microreactor',
                     'table':'temperature_microreactor_bufferturbo'},
                    #{'name': 'Time Of Flight',      
                    # 'table':'temperature_tof_turbopump'},
                    {'name': 'STM312 Big Turbo',
                     'table': 'temperature_stm312_big_turbo'},
                    {'name': 'STM312 gas handling',
                     'table': 'temperature_stm312_gas_handling'},
                    {'name': 'STM312 sputter gun',
                     'table': 'temperature_stm312_sputter_gun'}]

    # Fetch the data
    for meas in measurements:
        # Form the two queries ...
        queries = {'now': ('SELECT unix_timestamp(time), temperature FROM {0} '
                           'where temperature between -1 and 1300  order by '
                           'time desc limit 1;').format(meas['table']),
                   '1h_ago': ('SELECT unix_timestamp(time), temperature FROM '
                              '{0} where temperature between -1 and 1300 and '
                              'time between "{1}" and "{2}" order by time desc '
                              'limit 1;').format(meas['table'], start, end)}
        for key, value in queries.items():
            cursor.execute(value)
            data = cursor.fetchall()
            # If there is no data, fill in Nones
            meas[key] = data[0] if len(data) > 0 else (None, None)
    
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

def format_measurements(measurements):
    """ Format the measurements into nice text """
    import time
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

##############################################################################
# Main code                                                                  #
##############################################################################

import time
import sys

# The big try-except sends an email if the script generates an exception, 
# provided it is not in the send email part..!
try:
    # Temporary log
    with open('/tmp/cooling_water_surveillance_log', 'a') as f:
        f.write(time.strftime('%Y-%m-%d %H:%M') + '\n')

    # This is the value used for the comparisons
    tolerance_raise = 4
    tolerance_fall = 3
    absolute_max = 33 # Temporarily set to 33 (normal 30) while the cooling water is being fixed
    number_of_errors_trigger = 3
    cronjob_interval = 10 # min
    alert_count, measurements = cooling_water_check(tolerance_raise, tolerance_fall, absolute_max)

    # Danger, Will Robinson
    # To many measurements have generated alerts
    if alert_count >= number_of_errors_trigger:
        subject = 'CINF COOLING WATER ALERT'
        message = ('The survelliance of the COOLING WATER has TRIGGERED AN '
                   'ALERT, because {0} temperature measurements of turbo '
                   'pumps are out of bounds.\n\n'
                   'The bounds are:\n'
                   'Temperature has risen more than: {1}C with the last hour\n'
                   'Temperature has fallen more than: {2}C within the last hour\n'
                   'Temperature temperature has crossed the hard absolute limit of: {3}C\n'
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
                   'on campusnet.').format(alert_count,
                                           tolerance_raise,
                                           tolerance_fall,
                                           absolute_max,
                                           format_measurements(measurements))

        send_email(message, subject, warning=True)
    else:
        # Everything is good
        if (time.localtime().tm_hour == 12 and
            time.localtime().tm_min < cronjob_interval):
            # It's time to go to lunch
            subject = 'Cooling water surveillance check-in'
            message = ('I\'m the friendly cooling water surveillance script '
                       'checking in to let you know that I\'m running just '
                       'fine.\n'
                       '\n'
                       'The values of the temperature measurements at last '
                       'check was:\n'
                       '{0}'
                       '\n'
                       'And now it\'s time to go to lunch!').format(format_measurements(measurements))
                   
            send_email(message, subject, warning=False)
except:
    message = 'The script generated the following exception:\n\n' + str(sys.exc_info())
    subject = 'Cooling water surveillance script generated an error'
    send_email(message, subject, warning=False)
        
