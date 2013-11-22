#!/usr/bin/python

from optparse import OptionParser
import sys
import hashlib

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

class Plot():
    """ General description """
    
    def __init__(self):
        """ Description of init """

        # Create and parse the options
        parser = OptionParser()
        # Add the option names
        parser.add_option("-a", "--type")
        parser.add_option("-b", "--idlist")
        parser.add_option("-c", "--from_d")
        parser.add_option("-d", "--to_d")
        parser.add_option("-e", "--xmin")
        parser.add_option("-f", "--xmax")
        parser.add_option("-g", "--ymin")
        parser.add_option("-i", "--ymax")
        parser.add_option("-j", "--offset")
        parser.add_option("-k", "--as_function_of_t")
        parser.add_option("-l", "--logscale")
        parser.add_option("-m", "--shift_temp_unit")
        parser.add_option("-n", "--flip_x")
        parser.add_option("-o", "--shift_be_ke")
        parser.add_option("-p", "--size")

        (options, args) = parser.parse_args()
        # For use in other methods
        self.options = options

        ### Process options
        # Fetch idlist
        self.idlist = [int(element) for element in
                       options.idlist.split(',')[1:]]
        # Turn the offset "key:value," pair string into a dictionary
        self.offsets =  dict([[int(offset.split(':')[0]), offset.split(':')[1]]
                              for offset in options.offset.split(',')[1:]])
        # Turn as_function_of_t into boolean
        self.as_function_of_t = True if options.as_function_of_t ==\
            'checked' else False
        self.shift_temp_unit = True if options.shift_temp_unit ==\
            'checked' else False
        self.logscale = True if options.logscale == 'checked' else False
        self.flip_x = True if options.flip_x == 'checked' else False
        self.shift_be_ke = True if options.shift_be_ke == 'checked' else False

        ### Create db object # ADD MORE OPTIONS
        self.from_to = {'from':options.from_d, 'to':options.to_d}
        self.db = dataBaseBackend(typed=options.type, from_to=self.from_to,
                                  id_list=self.idlist, offsets=self.offsets,
                                  as_function_of_t=self.as_function_of_t,
                                  shift_temp_unit=self.shift_temp_unit,
                                  shift_be_ke=self.shift_be_ke)

        self.standard_sizes = {'small':'450x300', 'large':'4500x3000',
                               'def_size':'900x600'}
        
        # The 'name' is a string that is unique for this plot
        # Here we add all the information that is entered into the db object
        self.name = self.db.global_settings['chamber_name'] + '_' + options.type

        if options.from_d != '' or options.to_d != '':
            self.name += '_' + options.from_d + '_' + options.to_d

        self.name += ('_' + 'as_function_of_t') if self.as_function_of_t else ''
        self.name += ('_' + 'shift_temp_unit') if self.shift_temp_unit else ''
        self.name += ('_' + 'logscale') if self.logscale else ''
        self.name += ('_' + 'flip_x') if self.flip_x else ''
        self.name += ('_' + 'shift_be_ke') if self.shift_be_ke else ''

        if len(self.idlist) > 0:
            self.name += '_' + str(self.idlist)

        # object to give first good color, and then random colors
        self.c = color()

    def main(self):
        # Call a bunch of functions
        self._init_plot()
        self._plot()
        self._zoom_and_flip()
        self._transform_and_label_axis()
        self._title()
        self._grids()
        self._save()

    def _init_plot(self):
        if self.options.size in ['small', 'large', 'def_size']:
            self.size = self.db.global_settings[self.options.size] if\
                self.db.global_settings.has_key(self.options.size) else\
                self.standard_sizes[self.options.size]
        elif self.options.size == '':
            self.size = self.standard_sizes['def_size']
            
        # self.size is now a tuple of floats, size in inches
        self.size = tuple(float(e)/100 for e in self.size.split('x'))

        
        # Implicite, but may later be used to change the resolution
        dpi=100
        plt.figure(1, dpi=dpi, figsize=(self.size[0], self.size[1]))
        plt.subplot(111)

        # Decide on the y axis type
        self.gs = self.db.global_settings
        
        if self.options.type == 'morning_pressure':
            self.myplot = plt.bar
            plt.yscale('log')
        elif self.logscale:
            self.myplot = plt.semilogy
            self.name += '_semilog'
        elif self.gs['default_yscale'] == 'log':
            self.myplot = plt.semilogy
            self.name += '_semilog'
        else:
            self.myplot = plt.plot
            self.name += '_linear'
    
    def _plot(self):
        # Make plot
        for data in self.db.get_data():
            #print data
            st = 'color=self.c.get_color()'
            self.myplot(data['data'][:,0], data['data'][:,1],
                            color=self.c.get_color())
            
    def _zoom_and_flip(self):
        # Now we are done with the plotting, change axis if necessary
        # Get current axis limits
        self.axis = plt.axis()
        if self.options.xmin != self.options.xmax:
            self.axis = (float(self.options.xmin), float(self.options.xmax)) +\
                self.axis[2:4]
        if self.options.ymin != self.options.ymax:
            self.axis = self.axis[0:2] + (float(self.options.ymin),
                                          float(self.options.ymax))
        if self.flip_x:
            self.axis = (self.axis[1], self.axis[0]) + self.axis[2:4]
        plt.axis(self.axis)
        # Add information to name
        self.name += '_' + 'manualscale_' + str(self.axis)
        
    def _transform_and_label_axis(self):
        """ Transform X-AXIS axis and label it """
        
        # If it is a date plot
        if self.from_to['from'] or self.from_to['to']:
            # Turn the x-axis into timemarks
            # IMPLEMENT add something to TimeMarks initialisation to take care
            # or morning_pressure
            timemarks = TimeMarks(self.axis[0], self.axis[1])
            (old_tick_labels, new_tick_labels) = timemarks.get_time_marks()
            plt.xticks(old_tick_labels, new_tick_labels, rotation=25,\
                           horizontalalignment='right')
            # Make a little extra room for the rotated x marks
            plt.subplots_adjust(bottom=0.12)
            self.name += '_' + str(new_tick_labels)
        elif self.options.type == 'masstime':
            gs_temp_unit = self.gs['temperature_unit']
            other_temp_unit = 'C' if gs_temp_unit == 'K' else 'K'
            cur_temp_unit = other_temp_unit if self.shift_temp_unit else\
                gs_temp_unit
            if self.as_function_of_t:
                plt.xlabel(self.gs['t_xlabel'] + cur_temp_unit)
            else:
                plt.xlabel(self.gs['xlabel'])
        elif self.options.type == 'xps':
            if self.shift_be_ke:
                plt.xlabel(self.gs['e_xlabel'])
            else:
                plt.xlabel(self.gs['xlabel'])
        else:
            plt.xlabel(self.gs['xlabel'])
        
        # Label Y-axis
        plt.ylabel(self.gs['ylabel'])
        self.name += '_' + self.gs['ylabel']

    def _title(self):
        """ TITLE """
        
        # We want a title that is a litle bigger than default, and raised a bit
        # (3%)
        plt.title(self.gs['title'], fontsize=24, y=1.03)
        if self.as_function_of_t:
            plt.title(self.gs['t_title'], fontsize=24, y=1.03)
        else:
            plt.title(self.gs['title'], fontsize=24, y=1.03)
            
    def _grids(self):
        # GRIDS
        plt.grid(b=True, which = 'major')
        #plt.xscale('linear')
        #plt.xticks(range(0,100,10))
        #plt.x_minor_ticks(range(0,100,10))
        #plt.grid(b='on', which='minor')
        #plt.grid(b='on', which='major')

    def _save(self):
        ## Filesave
        # Create a hash of the name variable and use that as the file name
        hash = hashlib.md5()
        hash.update(self.name)
        namehash = '/var/www/figures/' + hash.hexdigest() + '.png'
        plt.savefig(namehash)

        filename = namehash
        f = open(filename, 'rb')
        # This is the magical line that plot.php opens
        # For the script to work this has to be the only print statement
        print namehash

if __name__ == "__main__":
    plot = Plot()
    plot.main()
