#!/usr/bin/python

from optparse import OptionParser
import sys
import hashlib

# set HOME environment variable to a directory the httpd server can write to
import os
os.environ[ 'HOME' ] = '/var/www/cinfdata/figures'
# System-wide ctypes cannot be run by apache... strange...
sys.path.insert(1, '/var/www/cinfdata')

# Matplotlib must be imported before MySQLdb (in dataBaseBackend), otherwise we
# get an ugly error
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt
import matplotlib.transforms as mtransforms

# Import our own classes
from databasebackend import dataBaseBackend
from common import Color, TimeMarks

class Plot():
    """This class is used to generate the figures for the plots."""
    
    def __init__(self):
        """ Description of init """

        # Create the option parser for the command line options
        usage = ('usage: %prog [options]\n\n'
                 'All options are strings. Boolean options are true when they \n'
                 'contains a certain specific keywords, which is written in \n'
                 'the option description in parantheses.')
        parser = OptionParser(usage=usage)

        # Add the options to the option parser
        parser.add_option('-a', '--type', help='Type string from '
                          'graphsettings.xml')
        parser.add_option('-b', '--idlist', help='List of id\'s to plot')
        parser.add_option('-c', '--from_d', help='From timestamp, format: '
                          'YYYY-MM-DD HH:MM')
        parser.add_option('-d', '--to_d', help='To timestamp, format: '
                          'YYYY-MM-DD HH:MM')
        parser.add_option('-e', '--xmin', help='X-min for zoom')
        parser.add_option('-f', '--xmax', help='X-max for zoom')
        parser.add_option('-g', '--ymin', help='Y-min for zoom')
        parser.add_option('-i', '--ymax', help='Y-max for zoom')
        parser.add_option('-j', '--offset', help='List of offsets for the '
                          'graphs (for plots that goes on a log scale and has '
                          'negative values)')
        parser.add_option('-k', '--as_function_of_t', help='Plot the graphs as '
                          'a function of temperature (boolean \'checked\'=True)')
        parser.add_option('-l', '--logscale', help='Use a log for the right '
                          'axis (boolean \'checked\'=True)')
        parser.add_option('-m', '--shift_temp_unit', help='Change between K '
                          'and C when values are plotted as a function of '
                          'temperature (boolean \'checked\'=True)')
        parser.add_option('-n', '--flip_x', help='Exchange min and max for the '
                          'x-axis (boolean \'checked\'=True)')
        parser.add_option('-o', '--shift_be_ke', help='Shift between binding '
                          'energy and kinetic energy for XPS plots (boolean '
                          '\'checked\'=True)')
        # -p is availabel from previous options
        parser.add_option('-q', '--image_format', help='Image format for the '
                          'figure exports, given as the figure extension. Can '
                          'be svg, eps, ps, pdf and default. Default means use '
                          'the one in graphsettings.xml or internal deaault.')
        parser.add_option('-r', '--small_plot', help='Produce a small plot '
                          '(boolean \'checked\'=1)')

        # Parse the options
        (options, args) = parser.parse_args()

        ### Process options - all options are given as string, and they need to
        ### be converted into other data types
        # Convert idlist
        self.idlist = [int(element) for element in options.idlist.split(',')[1:]]
        # Turn the offset 'key:value,' pair string into a dictionary
        self.offsets =  dict([[int(offset.split(':')[0]), offset.split(':')[1]]
                              for offset in options.offset.split(',')[1:]])
        # Gather from and to in a fictionary
        self.from_to = {'from':options.from_d, 'to':options.to_d}
        # Turn several options into booleans
        self.as_function_of_t = True if options.as_function_of_t ==\
            'checked' else False
        self.shift_temp_unit = True if options.shift_temp_unit ==\
            'checked' else False
        self.logscale = True if options.logscale == 'checked' else False
        self.flip_x = True if options.flip_x == 'checked' else False
        self.shift_be_ke = True if options.shift_be_ke == 'checked' else False
        self.small_plot = True if options.small_plot == '1' else False
                
        ### Create database backend object
        self.db = dataBaseBackend(typed=options.type, from_to=self.from_to,
                                  id_list=self.idlist, offsets=self.offsets,
                                  as_function_of_t=self.as_function_of_t,
                                  shift_temp_unit=self.shift_temp_unit,
                                  shift_be_ke=self.shift_be_ke)

        ### Ask self.db for a measurement count
        measurement_count = self.db.get_data_count()

        # Set the image format to standard, overwite with gs value and again
        # options value if i exits
        if options.image_format:
            if options.image_format == 'default':
                if self.db.global_settings.has_key('image_format'):
                    self.image_format = self.db.global_settings['image_format']
                else:
                    self.image_format = 'png'
            else:
                self.image_format = options.image_format
        else:
            self.image_format = 'png'
        
        # Create a hash from the measurement_count, options and
        #self.db.global_settings
        hash = hashlib.md5()
        hash.update(str(options) + str(self.db.global_settings) +
                    str(measurement_count))
        # self.namehash is unique for this plot and will form the filename
        self.namehash = ('/var/www/cinfdata/figures/' + hash.hexdigest() + '.' +
                         self.image_format)
        
        # For use in other methods
        self.options = options
 
        # object to give first good color, and then random colors
        self.c = Color()

        self.left_color = 'black'
        self.right_color = 'black'


    def main(self):
        if os.path.exists(self.namehash) and False:
            print self.namehash
        else:
            # Call a bunch of functions
            self._init_plot()
            self._plot()
            if self.left_color != 'black':
                if self.right_color != 'black':
                    self.c.color_axis(self.ax1, self.ax2, self.left_color, self.right_color)
                else:
                    self.c.color_axis(self.ax1, None, self.left_color, None)
            self._legend()
            self._zoom_and_flip()
            self._transform_and_label_axis()
            if not self.small_plot:
                self._title()
            self._grids()
            self._save()

    def _init_plot(self):
        ### Apply settings        
        # Small plots
        if self.small_plot:
            # Apply default settings 
            plt.rcParams.update({'figure.figsize':[4.5, 3.0],
                                 'ytick.labelsize':'x-small',
                                 'xtick.labelsize':'x-small',
                                 'legend.fontsize':'x-small'})
            # Overwrite with values from graphsettings
            plt.rcParams.update(self.db.global_settings['rcparams_small'])
        else:
            plt.rcParams.update({'figure.figsize':[9.0, 6.0],
                                 'axes.titlesize':'24',
                                 'legend.fontsize':'small'})
            plt.rcParams.update(self.db.global_settings['rcparams_regular'])

        self.fig = plt.figure(1)

        self.ax1 = self.fig.add_subplot(111)
        self.ax2 = None

        # Decide on the y axis type
        self.gs = self.db.global_settings
        if self.logscale:
            self.ax1.set_yscale('log')
        elif self.gs['default_yscale'] == 'log':
            self.ax1.set_yscale('log')
    
    def _plot(self):
        # Make plot
        data_in_plot = False
        for data in self.db.get_data():
            if len(data['data']) > 0:
                data_in_plot = data_in_plot or True
                # Speciel case for barplots
                if self.db.global_settings.has_key('default_style') and\
                        self.db.global_settings['default_style'] == 'barplot':
                    self.ax1.bar(data['data'][:,0], data['data'][:,1],
                                 color=self.c.get_color())
                # Normal graph styles
                else:
                    # If the graph go on the right side of the plot
                    if data['info']['on_the_right']:
                        # Initialise secondary plot if it isn't already
                        if not self.ax2:
                            self._init_second_y_axis()
                        # If info has a color (i.e. it is given in gs ordering)
                        if data['info'].has_key('color'):
                            # Set the color for the graph and axis
                            color = data['info']['color']
                            self.right_color = data['info']['color']
                        else:
                            # Else get a new color from self.c
                            color = self.c.get_color()
                        
                        # Make the actual plot
                        self.ax2.plot(data['data'][:,0], data['data'][:,1],
                                      color=color,
                                      label=self._legend_item(data)+'(R)')
                    # If the graph does not go on the right side of the plot
                    else:
                        # If info has a color (i.e. it is given in gs ordering)
                        if data['info'].has_key('color'):
                            # Set the color for the graph and axis
                            color = data['info']['color']
                            self.left_color = data['info']['color']
                        else:
                            # Else get a new color from self.c
                            color = self.c.get_color()
                        
                        # Make the actual plot
                        self.ax1.plot(data['data'][:,0], data['data'][:,1],
                                      color=color,
                                      label=self._legend_item(data))
        
        # If no data has been been put on the graph at all, explain why there
        # is none
        if not data_in_plot:
            y = 0.00032 if self.logscale or self.gs['default_yscale'] == 'log' else 0.5
            self.ax1.text(0.5, y, 'No data', horizontalalignment='center',
                          verticalalignment='center', color='red', size=60)

    def _legend(self):
        if self.db.global_settings['default_xscale'] != 'dat':
            ax1_legends = self.ax1.get_legend_handles_labels()
            if self.ax2:
                ax2_legends = self.ax2.get_legend_handles_labels()
                for color, text in zip(ax2_legends[0], ax2_legends[1]):
                    ax1_legends[0].append(color)
                    ax1_legends[1].append(text)
                    
            # loc for locations, 0 means 'best'. Why that isn't deafult I
            # have no idea
            self.ax1.legend(ax1_legends[0], ax1_legends[1], loc=0)

    def _zoom_and_flip(self):
        # Now we are done with the plotting, change axis if necessary
        # Get current axis limits
        self.axis = self.ax1.axis()
        if self.options.xmin != self.options.xmax:
            self.axis = (float(self.options.xmin), float(self.options.xmax)) +\
                self.axis[2:4]
        if self.options.ymin != self.options.ymax:
            self.axis = self.axis[0:2] + (float(self.options.ymin),
                                          float(self.options.ymax))
        if self.flip_x:
            self.axis = (self.axis[1], self.axis[0]) + self.axis[2:4]
        self.ax1.axis(self.axis)
        
    def _transform_and_label_axis(self):
        """ Transform X-AXIS axis and label it """
        
        # If it is a date plot
        if self.db.global_settings['default_xscale'] == 'dat':
            # Turn the x-axis into timemarks
            # IMPLEMENT add something to TimeMarks initialisation to take care
            # or morning_pressure
            markformat = '%H:%M' if self.small_plot else '%b-%d %H:%M'
            timemarks = TimeMarks(self.axis[0], self.axis[1],
                                  markformat=markformat)
            (old_tick_labels, new_tick_labels) = timemarks.get_time_marks()
            self.ax1.set_xticks(old_tick_labels)
            self.bbox_xlabels = self.ax1.\
                set_xticklabels(new_tick_labels, rotation=25,
                                horizontalalignment='right')
            # Make a little extra room for the rotated x marks
            #self.fig.subplots_adjust(bottom=0.12)
        elif self.options.type == 'masstime':
            gs_temp_unit = self.gs['temperature_unit']
            other_temp_unit = 'C' if gs_temp_unit == 'K' else 'K'
            cur_temp_unit = other_temp_unit if self.shift_temp_unit else\
                gs_temp_unit
            if self.as_function_of_t and not self.small_plot:
                self.ax1.set_xlabel(self.gs['t_xlabel'] + cur_temp_unit)
            elif not self.small_plot:
                self.ax1.set_xlabel(self.gs['xlabel'])
        elif self.options.type == 'xps':
            if self.shift_be_ke and not self.small_plot:
                self.ax1.set_xlabel(self.gs['alt_xlabel'])
            elif not self.small_plot:
                self.ax1.set_xlabel(self.gs['xlabel'])
        elif not self.small_plot:
            self.ax1.set_xlabel(self.gs['xlabel'])
        
        # Label Y-axis
        if not self.small_plot:
            self.ax1.set_ylabel(self.gs['ylabel'], color=self.left_color)
            if self.ax2:
                self.ax2.set_ylabel(self.gs['right_ylabel'], color=self.right_color)

    def _title(self):
        """ TITLE """
        # Set the title and raise it a bit
        if self.as_function_of_t:
            self.bbox_title = self.ax1.set_title(
                self.gs['t_title'], y=1.03)
        else:
            self.bbox_title = self.ax1.set_title(
                self.gs['title'], y=1.03)
            
    def _grids(self):
        # GRIDS
        self.ax1.grid(b=True, which = 'major')
        #plt.xscale('linear')
        #plt.xticks(range(0,100,10))
        #plt.x_minor_ticks(range(0,100,10))
        #plt.grid(b='on', which='minor')
        #plt.grid(b='on', which='major')

    def _save(self):
        ## Filesave
        # Save
        self.fig.savefig(self.namehash, bbox_inches='tight', pad_inches=0.03)

        # This is the magical line that plot.php opens
        # For the script to work this has to be the only print statement
        print self.namehash

    ### Here start the small helper functions that are called from the main flow

    def _init_second_y_axis(self):
        self.ax2 = self.ax1.twinx()
        if self.db.global_settings['right_yscale'] == 'log':
            self.ax2.set_yscale('log')

    def _legend_item(self, data):
        if self.db.global_settings['default_xscale'] == 'dat':
            return ''
        elif data['gs'].has_key('legend_field_name') and\
                data['info'][data['gs']['legend_field_name']]:
            return data['info']['mass_label'] + '-' + str(data['info']['id'])
        else:
            return str(data['info']['id'])

if __name__ == "__main__":
    #import cProfile
    #import pstats
    plot = Plot()
    plot.main()
    #cProfile.run('plot.main()', 'prof')
    #p = pstats.Stats('prof')
    #print p.sort_stats('time').get_top_level_stats().print_stats()
    #p.strip_dirs().sort_stats('time').print_stats()
    #p.sort_stats('time').print_stats()
    #p.sort_stats('cumulative').print_stats(30)
