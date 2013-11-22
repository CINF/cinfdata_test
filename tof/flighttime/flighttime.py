#!/usr/bin/python

# set HOME environment variable to a directory the httpd server can write to                                                                          
import os
os.environ[ 'HOME' ] = '/var/www/cinfdata/figures'
# System-wide ctypes cannot be run by apache... strange...                                                                                            sys.path.insert(1, '/var/www/cinfdata')


import sys
#import argparse
import optparse

import tof_model as tm
import tof_helpers 


parser = optparse.OptionParser('Calculates expected flighttime in the TOF')
parser.add_option('--html', action="store_true", default=False, help='Adds html line breaks to the text output')
parser.add_option('--liner', '-L', action="store", default=-2000, type=int, help='Liner Voltage')
parser.add_option('--pulse', '-P', action="store", default=400, type=int, help='Pulse Voltage')
parser.add_option('--R1', action="store", default=-432, type=int, help='Voltage at R1')
parser.add_option('--R2', action="store", default=1791, type=int, help='Voltage at R2')
(options, args) = parser.parse_args()


tm.Voltages = {} # Contains all electrical values of the maching
tm.Voltages['pulse'] = options.pulse
tm.Voltages['liner'] = options.liner
tm.Voltages['R1'] = options.R1
tm.Voltages['R2'] = options.R2

if tm.Voltages['R2'] <= 0: # Liniar mode
    tm.Voltages['R1'] = Voltages['R1'] = Voltages['liner']



""" Under here starts random experimentation that needs to be cleaned up """


#tof_helpers.extrapolate()

print(tof_helpers.print_flighttimes(True,True,False))


#R1_Voltage = 0
#R2_Voltage = 0


"""
flight_times = []
positions = []
mass = 28
for pos in range(int(FAST_POS*1000),int(SLOW_POS*1000)):
    res = flight_time(mass,pos/1000.0)
    positions.append(pos/1000.0)
    flight_times.append(res[0]*1e6)

fig = plt.figure()
ax11 = fig.add_subplot(111)
ax11.plot(positions,flight_times,'r-')
ax11.set_xlabel('Start position')
ax11.set_ylabel('Flight Time / micro seconds')
plt.savefig('Peak-width.png')
"""


#draw_trajectory(28.006)

#print_flighttimes(options.html,True,True)
#print_flighttimes(options.html,False,False)

#### Implement peak-width analysis!!!
# Should be easy...


#mass = 27.995
#res = flight_time(mass)
#print "Center ions {0}: {1:.3f} microsceonds".format(mass,res[0]*1e6)

#mass = 28.006
#res2 = flight_time(mass)
#print "Center ions {0}: {1:.3f} microsceonds".format(mass,res2[0]*1e6)

#print "Difference: {0}: ".format((res2[0]-res[0])*1e9)


#res = flight_time(mass,0.63)
#print "Slowest ions {0}: {1:.3f} microsceonds".format(mass,res[0]*1e6)
#res = flight_time(mass,-0.63)
#print "Fastest ions {0}: {1:.3f} microsceonds".format(mass,res[0]*1e6)

#R1_Voltage = 0
#R2_Voltage = 0
#res = flight_time(mass)
#print "Center ions {0}: {1:.3f} microsceonds".format(mass,res[0]*1e6)
#res = flight_time(mass,0.63)
#print "Slowest ions {0}: {1:.3f} microsceonds".format(mass,res[0]*1e6)
#res = flight_time(mass,-0.63)
#print "Fastest ions {0}: {1:.3f} microsceonds".format(mass,res[0]*1e6)

