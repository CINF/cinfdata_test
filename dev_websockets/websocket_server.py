# pylint: disable=W0142,W0603
""" This file implements the central websockets server for servcinf """

from os import path
import os
import time
import threading
import socket
import graphsettings
import tornado.ioloop
import tornado.web
import tornado.websocket


WEBDIR = '/var/www/cinfdata'
TIME_REPORT_ALIVE = 60  # Seconds between the thread reporting in
DATA = {}
TIME0 = time.time()
NUMBER_OF_WEBSOCKETS = 0
WS_ERROR = u'*ERR* {0}'

# socket object has close method
# socket.settimeout(1)
# will raise socket.timeout on timeout


def log(type_, string):
    """ Log events to files """
    delta_time = time.time() - TIME0
    print '{0:.<19}:{1: >11.2f} {2}'.format(type_, delta_time, string)


class UDPConnection(threading.Thread):
    """ UDP connection to a data logging client """
    def __init__(self, ip_port):
        threading.Thread.__init__(self)
        log(ip_port, '__init__ start')
        self._stop = False
        self._ip_port = ip_port
        self.ip_address, self.port = ip_port.split(':')
        self.port = int(self.port)
        self.socket = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        self.socket.settimeout(1)
        self.sane_interval = 1.0  # In seconds
        self.get_count = 0
        log(ip_port, '__init__ end')

    def run(self):
        """ Collect data """
        log(self._ip_port, 'run start')
        # Get sane interval
        data = self._send_and_get('get_sane_interval')
        if not self._stop:
            log(self._ip_port, 'run get_sane_interval')
            self.sane_interval = float(data) / 1000  # Convert from ms
            DATA[self._ip_port]['sane_interval'] = data
        # Get data fields
        data = self._send_and_get('get_fields')
        if not self._stop:
            log(self._ip_port, 'run get_fields')
            DATA[self._ip_port]['fields'] = data
        # Get data continously
        log(self._ip_port, 'run get_data continously')
        while not self._stop:
            self.get_count += 1
            data = self._send_and_get('get_data')
            DATA[self._ip_port]['data'] = data
            time.sleep(self.sane_interval)
            if self.get_count >= TIME_REPORT_ALIVE / self.sane_interval:
                log(self._ip_port,
                    'fetched {0} data points'.format(self.get_count))
                self.get_count = 0
        log(self._ip_port, 'run stopped')

    def _send_and_get(self, command):
        """ Send command and get response """
        self.socket.sendto(command, (self.ip_address, self.port))
        try:
            data, _ = self.socket.recvfrom(1024)
        except socket.timeout:
            print 'timeout'
            self.stop()
            data = None
        return data

    def stop(self):
        """ Stop the UDP connection """
        log(self._ip_port, 'stop')
        self._stop = True


class UDPConnectionSteward(threading.Thread):
    """ Class that creates and manages the UDP connections """

    def __init__(self):
        log('STEWARD', '__init__ start')
        self._stop = False
        threading.Thread.__init__(self)
        self.udp_definitions = set()
        self.udp_connections = {}
        log('STEWARD', '__init__ end')

    def run(self):
        """ Run method """
        log('STEWARD', 'run start')
        time0 = time.time() - 3600
        while not self._stop:
            if time.time() - time0 > 3600:
                log('STEWARD', 'checking the connections')
                self._update_udp_definitions()
                udp_connection_keys = set(self.udp_connections.keys())
                add = self.udp_definitions - udp_connection_keys
                delete = udp_connection_keys - self.udp_definitions
                if self.udp_definitions == udp_connection_keys:
                    log('STEWARD', 'Connections up to date')
                for ip_port in add:
                    log('STEWARD', 'adding connection: {0}'.format(ip_port))
                    self.udp_connections[ip_port] = UDPConnection(ip_port)
                    DATA[ip_port] = {'data': None, 'fields': None,
                                     'sane_interval': None}
                    self.udp_connections[ip_port].start()
                for ip_port in delete:
                    log('STEWARD', 'Deleting connection: {0}'.format(ip_port))
                    del self.udp_connections[ip_port]
                    del DATA[ip_port]
                time0 = time.time()
            time.sleep(1)
        log('STEWARD', 'run stopped')

    def stop(self):
        """ Ask the thread to stop """
        log('STEWARD', 'stop')
        for ip_port, connection in self.udp_connections.items():
            log('STEWARD', 'stopping thread {0}'.format(ip_port))
            connection.stop()
        log('STEWARD', 'sleeping 1 seconds after stopping all threads')
        time.sleep(1)
        for ip_port, connection in self.udp_connections.items():
            if connection.is_alive():
                log('STEWARD',
                    'thread {0} i still alive, investigate!'.format(ip_port))
        self._stop = True

    def _update_udp_definitions(self):
        """ Scan graphsettings files for udp connection definitnions """
        log('STEWARD', 'scan graphsettings files')
        self.udp_definitions.clear()
        for item in os.listdir(WEBDIR):
            testpath = path.join(WEBDIR, item, 'graphsettings.xml')
            if path.isfile(testpath):
                self._add_udp_def_from_file(testpath)

    def _add_udp_def_from_file(self, filepath):
        """ Get the udp definitions from a single file """
        settings_object = graphsettings.graphSettings(filepath=filepath)
        settings = settings_object.get_settings()
        short_name = os.sep.join(filepath.split(os.sep)[-2:])
        try:
            for server in settings['sockets'].values():
                log('STEWARD', 'found {0} in {1}'.format(server, short_name))
                if server not in self.udp_definitions:
                    self.udp_definitions.add(server)
        except KeyError:
            pass


class CinfWebSocketHandler(tornado.websocket.WebSocketHandler):
    """ The websocket server that handles all websocket connections """

    def open(self):
        """ On open connection """
        global NUMBER_OF_WEBSOCKETS
        NUMBER_OF_WEBSOCKETS += 1
        log('WEBSOCKET', 'New opened, now {0}'.format(NUMBER_OF_WEBSOCKETS))

    def on_message(self, message):
        """ On message. The message should be on the form ip:port;request """
        # log('WEBSOCKET', 'Message')
        # List of [ip:port, request]
        message = message.split(';')

        if len(message) != 2:
            answer = WS_ERROR.format('Bad request, should be: ip:port;request')
        elif message[0] not in DATA:
            answer = WS_ERROR.format('No connection {0}'.format(message[0]))
        elif message[1] not in DATA[message[0]]:
            answer = WS_ERROR.format('No request item {0} for connection {1}'.\
                                         format(message[1], message[0]))
        else:
            answer = '{0};{1}'.format(message[1], DATA[message[0]][message[1]])
        self.write_message(answer)

    def on_close(self):
        global NUMBER_OF_WEBSOCKETS
        NUMBER_OF_WEBSOCKETS -= 1
        log('WEBSOCKET',
            'Exiting closed, now {0}'.format(NUMBER_OF_WEBSOCKETS))


def main():
    """ Main method for the websocket server """
    log('MAIN', 'Starting')
    udp_steward = UDPConnectionSteward()
    udp_steward.start()
    import ssl
    try:
        ssl_options = {
            #'ssl_version': ssl.PROTOCOL_SSLv23,
            #'certfile': '/var/lib/kenni/server.crt',
            'certfile': '/var/lib/kenni/fysik.dtu.dk.crt',
            #'certfile': '/var/lib/kenni/intermediate.crt',
            'keyfile': '/var/lib/kenni/fysik.dtu.dk.key'
            }
        application = tornado.web.Application(
            [(r'/websocket', CinfWebSocketHandler)],
            #ssl_options=ssl_options
            )
        application.listen(8888,
                           #ssl_options=ssl_options
                           )
        tornado.ioloop.IOLoop.instance().start()
    except KeyboardInterrupt:
        udp_steward.stop()
        log('MAIN', 'Exiting!')

if __name__ == '__main__':
    main()
