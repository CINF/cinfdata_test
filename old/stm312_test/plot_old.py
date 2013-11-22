#!/usr/bin/python

from optparse import OptionParser
import sys
import hashlib

# THIS IS NOT NICE
# set HOME environment variable to a directory the httpd server can write to
import os
os.environ[ 'HOME' ] = '/var/www/stm312_test'

# Matplotlib must be imported before MySQLdb (in dataBaseBackend), otherwise we
# get an ugly error
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt

from databasebackend import dataBaseBackend
from common import color, TimeMarks

################################################################################
# Initialise                                                                   #
################################################################################

class plot():
    """ General description """
    

# Create and parse the options
parser = OptionParser()
# Add the option names
parser.add_option("-t", "--type")
parser.add_option("-i", "--idlist")
parser.add_option("-f", "--from_d")
parser.add_option("-z", "--to_d")
parser.add_option("-a", "--xmin")
parser.add_option("-b", "--xmax")
parser.add_option("-c", "--ymin")
parser.add_option("-d", "--ymax")
parser.add_option("-o", "--offset")
parser.add_option("-e", "--as_function_of_t")
parser.add_option("-l", "--logscale")
parser.add_option("-s", "--shift_temp_unit")
parser.add_option("-g", "--flip_x")
parser.add_option("-j", "--shift_be_ke")

(options, args) = parser.parse_args()

### Process options
# Fetch idlist
idlist = [int(element) for element in options.idlist.split(',')[1:]]
# Turn the offset "key:value," pair string into a dictionary
offsets =  dict([[int(offset.split(':')[0]), offset.split(':')[1]] for
                    offset in options.offset.split(',')[1:]])
# Turn as_function_of_t into boolean
as_function_of_t = True if options.as_function_of_t == 'checked' else False
shift_temp_unit = True if options.shift_temp_unit == 'checked' else False
logscale = True if options.logscale == 'checked' else False
flip_x = True if options.flip_x == 'checked' else False
shift_be_ke = True if options.shift_be_ke == 'checked' else False

# Create db object # ADD MORE OPTIONS
from_to = {'from':options.from_d, 'to':options.to_d}
db = dataBaseBackend(typed=options.type, from_to=from_to, id_list=idlist,
                     offsets=offsets, as_function_of_t=as_function_of_t,
                     shift_temp_unit=shift_temp_unit, shift_be_ke=shift_be_ke)

# The 'name' is a string that is unique for this plot
# Here we add all the information that is entered into the db object
name = db.global_settings['chamber_name'] + '_' + options.type

if options.from_d != '' or options.to_d != '':
    name += '_' + options.from_d + '_' + options.to_d

name += ('_' + 'as_function_of_t') if as_function_of_t else ''
name += ('_' + 'shift_temp_unit') if shift_temp_unit else ''
name += ('_' + 'logscale') if logscale else ''

if len(idlist) > 0:
    name += '_' + str(idlist)

################################################################################
# Functions                                                                    #
################################################################################


################################################################################
# Fetch data and info                                                          #
################################################################################

# Implicite, but may later be used to change the resolution
plt.figure(1)
plt.subplot(111)

# Decide on the y axis type
gs = db.global_settings
if logscale:
    myplot = plt.semilogy
    name += '_semilog'
elif gs['default_yscale'] == 'log':
    myplot = plt.semilogy
    name += '_semilog'
else:
    myplot = plt.plot
    name += '_linear'

# object to give first good color, and then random colors
c = color()

# Make plot
for data in db.get_data():
    myplot(data['data'][:,0], data['data'][:,1], color=c.get_color())

# Now we are done with the plotting, change axis if necessary
# Get current axis limits
axis = plt.axis()
if options.xmin != options.xmax:
    axis = (float(options.xmin), float(options.xmax)) + axis[2:4]
if options.ymin != options.ymax:
    axis = axis[0:2] + (float(options.ymin), float(options.ymax))
if flip_x:
    axis = (axis[1], axis[0]) + axis[2:4]
plt.axis(axis)
# Add information to name
name += '_' + 'manualscale_' + str(axis)

# Transform X-AXIS axis and label it
if options.type == 'pressure' or options.type == 'temperature':
    # Turn the x-axis into timemarks
    axis = plt.axis()
    timemarks = TimeMarks(axis[0], axis[1])
    (old_tick_labels, new_tick_labels) = timemarks.get_time_marks()
    plt.xticks(old_tick_labels, new_tick_labels, rotation=25,\
                   horizontalalignment='right')
    plt.subplots_adjust(bottom=0.11)
elif options.type == 'morning_pressure':
    # Do something here
    pass
elif options.type == 'masstime':
    gs_temp_unit = gs['temperature_unit']
    other_temp_unit = 'C' if gs_temp_unit == 'K' else 'K'
    cur_temp_unit = other_temp_unit if shift_temp_unit else gs_temp_unit
    if as_function_of_t:
        plt.xlabel(gs['t_xlabel'] + cur_temp_unit)
    else:
        plt.xlabel(gs['xlabel'])
elif options.type == 'xps':
    if shift_be_ke:
        plt.xlabel(gs['e_xlabel'])
    else:
        plt.xlabel(gs['xlabel'])
else:
    plt.xlabel(gs['xlabel'])


# Y-AXIS
plt.ylabel(gs['ylabel'])

# TITLE
# We want a title that is a litle bigger than default, and raised a bit (3%)
plt.title(gs['title'], fontsize=24, y=1.03)
if as_function_of_t:
    plt.title(gs['t_title'], fontsize=24, y=1.03)
else:
    plt.title(gs['title'], fontsize=24, y=1.03)

# GRIDS
plt.grid(b=True, which = 'major')
#plt.xscale('linear')
#plt.xticks(range(0,100,10))
#plt.x_minor_ticks(range(0,100,10))
#plt.grid(b='on', which='minor')
#plt.grid(b='on', which='major')

## Filesave
# Create a hash of the name variable and use that as the file name
hash = hashlib.md5()
hash.update(name)
namehash = '/var/www/figures/' + hash.hexdigest() + '.png'
plt.savefig(namehash)


filename = namehash
f = open(filename, 'rb')
# This is the magical line that plot.php opens
# For the script to work this has to be the only print statement
print(namehash)
