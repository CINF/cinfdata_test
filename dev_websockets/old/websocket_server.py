#!/usr/bin/env python2

""" Websocket server for servcinf """

import socket

HOST, PORT = '130.225.87.213', 9999  # Volvi pressure
SOCKET = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)


def send_and_get(command):
    """ Send a command over the SOCKET and recieve the response """
    SOCKET.sendto(command + '\n', (HOST, PORT))
    received = SOCKET.recv(1024)
    return received


def main():
    """ Main method """
    pressure = send_and_get('read_pressure')
    print pressure


if __name__ == '__main__':
    main()
