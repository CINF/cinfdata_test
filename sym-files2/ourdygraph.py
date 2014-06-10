#!/usr/bin/python

"""
This file is part of the CINF Data Presentation Website
Copyright (C) 2012 Robert Jensen, Thomas Andersen and Kenneth Nielsen

The CINF Data Presentation Website is free software: you can
redistribute it and/or modify it under the terms of the GNU
General Public License as published by the Free Software
Foundation, either version 3 of the License, or
(at your option) any later version.

The CINF Data Presentation Website is distributed in the hope
that it will be useful, but WITHOUT ANY WARRANTY; without even
the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more
details.

You should have received a copy of the GNU General Public License
along with The CINF Data Presentation Website.  If not, see
<http://www.gnu.org/licenses/>.
"""

import time
import sys
from common import Color

class Plot():
    """ This class is used to generate the dygraph content.

    NOTE: With the version of dygraph used at the time of writing
    version 2 (medio 2012) it was not possible to produce parametric
    plots (for as function of temperature type plots) nor was it
    possible to flip the x axis be reversing the x-axis limits. For
    the latter there is a snippet of code in a comment in the end of
    the file if it is ever made possible."""

    def __init__(self, options, ggs):
        """ Initialize variables """
        self.o = options
        self.ggs = ggs # Global graph settings

        self.right_yaxis = len(self.o['right_plotlist']) > 0
        self.out = sys.stdout
        self.tab = '    '
        # object to give first good color, and then random colors
        self.c = Color()

    def new_plot(self, data, plot_info, measurement_count):
        """ Produce all the plot output by calls to the different
        subsection methods
        """
        self.measurement_count = sum(measurement_count)
        self._header(self.out, data, plot_info)
        self._data(self.out, data, plot_info)
        self._options(self.out, data, plot_info)
        self._end(self.out, data, plot_info)
        
    def _header(self, out, data, plot_info):
        """ Form the header """
        out.write('g = new Dygraph(\n' +
                  self.tab + 'document.getElementById("graphdiv"),\n') 

    def _data(self, out, data, plot_info):
        """ Determine the type of the plot and call the appropriate
        _data_*** function
        """
        if self.ggs['default_xscale'] == 'dat':
            self._data_dateplot(out, data, plot_info)
        else:
            self._data_xyplot(out, data, plot_info)

    def _data_dateplot(self, out, data, plot_info):
        plot_n = len(self.o['left_plotlist'] + self.o['right_plotlist'])
        last_line = '\n' + self.tab + '//DATA\n'
        for n, dat in enumerate(data['left'] + data['right']):
            this_line = [''] * (plot_n + 1)
            for item in dat['data']:
                out.write(last_line)
                this_line[0] = str(time.strftime('%Y-%m-%d %H:%M:%S',
                                                 time.localtime(int(item[0]))))
                this_line[n+1] = str(item[1])
                last_line = self.tab + '"' + ','.join(this_line) + '\\n" +\n'
        
        if self.measurement_count == 0:
            out.write(last_line)
            this_line = [str(42), str(42)]
            last_line = self.tab + '"' + ','.join(this_line) + '\\n" +\n'
        
        out.write(last_line.rstrip(' +\n') + ',\n\n')

    def _data_xyplot(self, out, data, plot_info):
        plot_n = len(self.o['left_plotlist'] + self.o['right_plotlist'])
        last_line = '\n' + self.tab + '//DATA\n'
        for n, dat in enumerate(data['left'] + data['right']):
            this_line = [''] * (plot_n + 1)
            for item in dat['data']:
                out.write(last_line)
                this_line[0] = str(item[0])
                this_line[n+1] = str(item[1])
                last_line = self.tab + '"' + ','.join(this_line) + '\\n" +\n'

        # Write one bogus points if there is no data
        if self.measurement_count == 0:
            out.write(last_line)
            this_line = [str(42), str(42)]
            last_line = self.tab + '"' + ','.join(this_line) + '\\n" +\n'
        
        out.write(last_line.rstrip(' +\n') + ',\n\n')

    def _options(self, out, data, plot_info):
        """ Form all the options and ask _output_options to print them in a
        javascript friendly way
        """

        def _q(string):
            """ _q for _quote: Utility function to add quotes to strings """
            return '\'{0}\''.format(string)

        # Form labels string
        labels = [self.ggs['xlabel'] if self.ggs.has_key('xlabel') else '']
        for dat in data['left']:
            labels.append(dat['lgs']['legend'])
        r_ylabels=[]
        for dat in data['right']:
            labels.append(dat['lgs']['legend'])
            r_ylabels.append(dat['lgs']['legend'])
        # Overwrite labels if there is no data
        if self.measurement_count == 0:
            labels = ['NO DATA X', 'NO DATA Y']

        # Initiate options variable. A group of options are contained in a list
        # and the options are given as a key value pair in in a dictionary
        # containing only one item
        options = [{'labels': str(labels)}]

        # Add second yaxis configuration
        two_line_y_axis_label = False
        if self.right_yaxis:
            first_label = r_ylabels[0]
            # Add first data set to secondary axis
            # Ex: 'Containment (r)': {axis: {}}
            y2options = [{'logscale': str(self.o['right_logscale']).lower()}]
            if self.o['right_yscale_bounding'] is not None:
                y2options.append(
                    {'valueRange': str(list(self.o['right_yscale_bounding']))}
                    )
            options.append({_q(first_label): [{'axis': y2options}]})
            # Add remaining datasets to secondary axis
            for label in r_ylabels[1:]:
                # Ex: 'IG Buffer (r)': {axis: 'Containment (r)'}
                a = {_q(label): [{'axis': _q(first_label)}]}
                options.append(a)

            # Add the right y label
            if plot_info.has_key('right_ylabel'):
                if plot_info['y_right_label_addition'] == '':
                    options.append({'y2label': _q(plot_info['right_ylabel'])})
                else:
                    two_line_y_axis_label = True
                    label = '<font size="3">{0}<br />{1}</font>'.format(
                        plot_info['right_ylabel'], plot_info['y_right_label_addition'])
                    options.append({'y2label':_q(label)})

        # General options
        options += [{'logscale': str(self.o['left_logscale']).lower()},
                    {'connectSeparatedPoints': 'true'},
                    {'legend': _q('always')},
                    #{'lineWidth': '0'}
                    ]
     
        # Add title
        if plot_info.has_key('title'):
            if self.measurement_count == 0:
                options.append({'title': _q('NO DATA')})
            else:
                options.append({'title': _q(plot_info['title'])})

        # Add the left y label
        if plot_info.has_key('left_ylabel'):
            if plot_info['y_left_label_addition'] == '':
                options.append({'ylabel': _q(plot_info['left_ylabel'])})
            else:
                two_line_y_axis_label = True
                label = '<font size="3">{0}<br />{1}</font>'.format(
                    plot_info['left_ylabel'], plot_info['y_left_label_addition'])
                options.append({'ylabel':_q(label)})

        # Set the proper space for y axis labels
        if two_line_y_axis_label:
            options.append({'yAxisLabelWidth': '100'})
        else:
            options.append({'yAxisLabelWidth': '80'})

        # Determine the labels and add them
        if plot_info.has_key('xlabel'):
            if self.ggs['default_xscale'] != 'dat':
                if plot_info['xlabel_addition'] == '':
                    options.append({'xlabel': _q(plot_info['xlabel'])})
                else:
                    label = '<font size="3">{0}<br />{1}</font>'.format(
                        plot_info['xlabel'], plot_info['xlabel_addition'])
                    options.append({'xlabel':_q(label)})
                    options.append({'xLabelHeight': '30'})

        colors = [self.c.get_color_hex() for dat in data['left'] + data['right']]
        options.append({'colors':str(colors)})
        
        # Zoom, left y-scale
        if self.o['left_yscale_bounding'] is not None:
            options.append({'valueRange':
                                str(list(self.o['left_yscale_bounding']))})
        # X-scale
        if self.o['xscale_bounding'] is not None and\
                self.o['xscale_bounding'][1] > self.o['xscale_bounding'][0]:
            xzoomstring = '[{0}, {1}]'.format(self.o['xscale_bounding'][0],
                                              self.o['xscale_bounding'][1])
            options.append({'dateWindow': xzoomstring})

        grids = [{'drawXGrid': 'true'}, {'drawYGrid': 'false'}]
        # Add modifications from settings file
        if self.ggs.has_key('dygraph_settings'):
            # roller
            if self.ggs['dygraph_settings'].has_key('roll_period'):
                period = self.ggs['dygraph_settings']['roll_period']
                options += [{'showRoller': 'true'}, {'rollPeriod': period}]
            # grids
            if self.ggs['dygraph_settings'].has_key('xgrid'):
                grids[0]['drawXGrid'] = self.ggs['dygraph_settings']['xgrid']
            if self.ggs['dygraph_settings'].has_key('ygrid'):
                grids[1]['drawYGrid'] = self.ggs['dygraph_settings']['ygrid']
            # high light series
            if self.ggs['dygraph_settings'].has_key('series_highlight'):
                if self.ggs['dygraph_settings']['series_highlight'] == 'true':
                    options.append(
                        {'highlightSeriesOpts': [
                                {'strokeWidth': '2'},
                                {'strokeBorderWidth': '1'},
                                {'highlightCircleSize': '5'}
                                ]
                         })
            if self.ggs['dygraph_settings'].has_key('labels_side'):
                if self.ggs['dygraph_settings']['labels_side'] == 'true':
                    sep_new_lines = 'true'
                    if self.ggs['dygraph_settings'].has_key('labels_newline'):
                        sep_new_lines = self.ggs['dygraph_settings']['labels_newline']
                    options += [{'labelsDiv': 'document.getElementById("labels")'},
                                {'labelsSeparateLines': sep_new_lines}]
            elif self.ggs['dygraph_settings'].has_key('labels_newline'):
                sep_new_lines = self.ggs['dygraph_settings']['labels_newline']
                options += [{'labelsSeparateLines': sep_new_lines}]

        # Disable grids
        options += grids


        self._output_options(out, None, options, 1, last=True)

    def _output_options(self, out, name, options, level, last=False):
        """ Oh boy! I wish I had documented this when I wrote it! KN """
        if name is None:
            out.write(self.tab * level + '{\n')
        else:
            out.write(self.tab * level + name + ': {\n')

        for n, (key, value) in enumerate([op.items()[0] for op in options]):
            if isinstance(value, list):
                if n == len(value)-1:
                    self._output_options(out, key, value, level+1, last=True)
                else:
                    self._output_options(out, key, value, level+1, last=False)
            else:
                if n == len(options)-1:
                    out.write(self.tab*(level+1) + key + ': ' + value + '\n')
                else:
                    out.write(self.tab*(level+1) + key + ': ' + value + ',\n')
                
        if last:
            out.write(self.tab * level + '}\n')
        else:
            out.write(self.tab * level + '},\n')

    def _end(self, out, data, plot_info):
        """ Output last line """
        out.write(');')

# Flip x is not possible with dygraph :( it simply does not show the
# data points
# x_min = x_max = yl_min = yl_max = ry_min = yl_max = None
#if self.o['flip_x']:
#    if len(data['left'] + data['right']) > 0:
#        x_min = min([min(dat['data'][:,0]) for dat in
#                     data['left'] + data['right']])
#        x_max = max([max(dat['data'][:,0]) for dat in
#                     data['left'] + data['right']])
#    if x_min is not None and x_max is not None:
#        options.append({'dateWindow':
#                            '[{0}, {1}]'.format(x_max, x_min)})
            
