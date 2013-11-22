#!/usr/bin/python

# set HOME environment variable to a directory the httpd server can write to
import os, sys
os.environ[ 'HOME' ] = '/var/www/cinfdata/figures'
# System-wide ctypes cannot be run by apache... strange...
sys.path.insert(1, '/var/www/cinfdata')

# Matplotlib must be imported before MySQLdb (in dataBaseBackend), otherwise we
# get an ugly error
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt

fig = plt.figure(1)
ax1 = fig.add_subplot(111)
ax1.plot([1,2,3])
fig.savefig(sys.stdout, format='png')
#print fig
#print filename
