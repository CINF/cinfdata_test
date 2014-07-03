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

from optparse import OptionParser
import sys
from time import strptime, strftime, time, localtime
import re

# set HOME environment variable to a directory the httpd server can write to
import os
os.environ[ 'HOME' ] = '/var/www/cinfdata/figures'
# System-wide ctypes cannot be run by apache... strange...
sys.path.insert(1, '/var/www/cinfdata')

# Matplotlib must be imported before MySQLdb (in dataBaseBackend), otherwise we
# get an ugly error
#from matplotlib import rc FIXME FIXME
#rc('text', usetex=True)   FIXME FIXME reenable when we have latex support
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt
#import matplotlib.transforms as mtransforms
#import matplotlib.dates as mdates

# Import our own classes
from databasebackend import dataBaseBackend

from graphsettings import graphSettings
import ourmatplotlib, ourdygraph


class Plot():
    """This class is used to generate the figures for the plots."""
    
    def __init__(self):
        """ Description of init """

        # Create optionparser
        parser = OptionParser()

        # Add the options to the option parser
        # Option help at https://cinfwiki.fysik.dtu.dk/cinfwiki/Software/
        # DataWebPageDeveloperDocumentation#plot.py
        parser.add_option('--type')                  # String option
	parser.add_option('--boolean_options')       # Boolean options
	parser.add_option('--left_plotlist')         # int list
	parser.add_option('--right_plotlist')        # int list
	parser.add_option('--xscale_bounding')       # Float pair
	parser.add_option('--left_yscale_bounding')  # Float pair
	parser.add_option('--right_yscale_bounding') # Float pair
	parser.add_option('--from_to')               # Time stamp pair NOT HANDLED
	parser.add_option('--image_format')          # String options
	parser.add_option('--manual_labels_n_titel') # Manual labels and title for mpl
	parser.add_option('--input_id')              # Database id for plugin input

        # Parse the options
        (options, args) = parser.parse_args()

        ### Process options into self.o -  all options are given as strings,
        ### and they need to be converted into other data types
        self.o = {}
        # Parse boolean options
        for pair in options.boolean_options.split(',')[1:]:
            key, value = pair.split(':')
            self.o[key] = True if value == 'checked' else False
        # Parse bounds
        bkeys = [s + '_bounding' for s in ['xscale', 'left_yscale', 'right_yscale']]
        for bound in bkeys:
            bounding = [b if b != '' else '0'
                        for b in options.__dict__[bound].split(',')]
            if bounding != ['0', '0']:
                self.o[bound] = tuple(
                    [float(b) for b in bounding]
                    )
            else:
                self.o[bound] = None
        # Parse lists
        for plotlist in ['left_plotlist', 'right_plotlist']:
            # List comprehension, split list string up and turn into integer
            # and add to new list, but only of > 0
            self.o[plotlist] = [int(a) for a in 
                                options.__dict__[plotlist].split(',')[1:]
                                if int(a) > 0]
        # Parse string options
        for key in ['type', 'image_format']:
            self.o[key] = options.__dict__[key]
        for opt in options.manual_labels_n_titel.split(','):
            self.o[opt.split('=')[0]] = opt.split('=')[1]
        # From_to
        self.o['from_to'] = options.from_to.split(',')
        # Database ID for plugin input
        self.o['input_id'] = int(options.input_id)
        ### Done processing options

        # Get a (g)eneral (g)raph (s)ettings object
        # (Are not polulated with data set specific values) 
        self.ggs = graphSettings(self.o['type']).settings

        # If a dateplot and called without (valid) datetimes fill them in
        try:
            strptime(self.o['from_to'][0], '%Y-%m-%d  %H:%M')
            strptime(self.o['from_to'][1], '%Y-%m-%d  %H:%M')
        except ValueError:
            start = time()-24*3600
            if self.ggs.has_key('default_time'):
                default_time = int(self.ggs['default_time'])
                start = time()-default_time*3600
                
            # [now-1d, now]
            self.o['from_to'][0] = strftime('%Y-%m-%d  %H:%M',
                                            localtime(start))
            self.o['from_to'][1] = strftime('%Y-%m-%d  %H:%M')

        ### Create database backend object
        self.db = dataBaseBackend(options=self.o, ggs=self.ggs)

        self.defaults = {}

    def main(self):
        # Import the plotting engine appropriate for the plot type
        if self.o['matplotlib'] is True:
            self.plot = ourmatplotlib.Plot(options=self.o, ggs=self.ggs)
        else:
            self.plot = ourdygraph.Plot(options=self.o, ggs=self.ggs)
        self.new_plot()

    def new_plot(self):
        """ To form a new plot we first:
        1) Fetch the data
        2) Determine the title
        3) Determine the label
        4) Ask the plotting engine to make the plot
        """
        self.data = self.db.get_data()
        self.plot_info = self.titles_and_labels(self.data)
        self.plot_info.update(self.data['data_treatment'])
        self.data = self.legends(self.data)
        measurement_count = [len(dat['data']) for dat in
                             self.data['left'] + self.data['right']]
        return self.plot.new_plot(self.data, self.plot_info,
                                  measurement_count)

    def titles_and_labels(self, data):
        """ Determin plot type and find titles and labels from the functions:
          _titles_and_labels_dateplot                                                                                                 
          _titles_and_labels_xyplot
        """
        if self.ggs['default_xscale'] == 'dat':
            titles_n_labels = self._titles_and_labels_dateplot(data)
        else:
            titles_n_labels = self._titles_and_labels_xyplot(data)

        return titles_n_labels

    def _titles_and_labels_dateplot(self, data, titles_n_labels={}):
        """ Determine title and labels for a date plot"""
        # Fall backs

        # Title
        # Pull out a title candidate for each of the data graphs
        if self.ggs.has_key('title'):
            fall_back = self.ggs['title'] if self.ggs.has_key('title') else ''

            title_cand = []
            for v in data['left'] + data['right']:
                if v['lgs'].has_key('title'):
                    title_cand.append(v['lgs']['title'])
            titles_n_labels['title'] =\
                self._reduce_candidates(title_cand, fall_back)

        # Same procedure for Y-labels
        if self.ggs.has_key('ylabel'):
            fall_back = self.ggs['ylabel'] if self.ggs.has_key('ylabel') else ''
            cand = {'left': [], 'right': []}
            for side in ['left', 'right']:
                for v in data[side]:
                    if v['lgs'].has_key('ylabel'):
                        cand[side].append(v['lgs']['ylabel'])

                titles_n_labels[side + '_ylabel'] = \
                    self._reduce_candidates(cand[side], fall_back)

        return titles_n_labels

    def _titles_and_labels_xyplot(self, data, titles_n_labels={}):
        """ Determine title and labels for a xyplot """
        for name in ['title', 'xlabel']:
            if self.ggs.has_key(name):
                titles_n_labels[name] = self.ggs[name] 

        if self.ggs.has_key('ylabel'):
            # Find y-label reg exps
            reg_exp_tags = [k for k in self.ggs['ylabel'].keys()
                            if k.find('pattern') == 0]
            reg_exp_tags.sort()
            ylabel_fall_back = self.ggs['ylabel']['default']

            # Left ylabel
            left_ylabel_cand = []
            for dat in data['left']:
                for tag in reg_exp_tags:
                    reg_exp = self.ggs['ylabel'][tag]['reg_match']
                    label = dat['meta'][self.ggs['ylabel']['column']]
                    search = re.search(reg_exp, label)
                    try:
                        if len(search.group(0)) > 0:
                            left_ylabel_cand.append(
                                self.ggs['ylabel'][tag]['ylabel'])
                    except AttributeError:
                        pass
            titles_n_labels['left_ylabel'] = self._reduce_candidates(
                left_ylabel_cand, ylabel_fall_back)

            # Right ylabel
            right_ylabel_cand = []
            for dat in data['right']:
                for tag in reg_exp_tags:
                    reg_exp = self.ggs['ylabel'][tag]['reg_match']
                    label = dat['meta'][self.ggs['ylabel']['column']]
                    search = re.search(reg_exp, label)
                    try:
                        if len(search.group(0)) > 0:
                            right_ylabel_cand.append(
                                self.ggs['ylabel'][tag]['ylabel'])
                    except AttributeError:
                        pass
            titles_n_labels['right_ylabel'] = self._reduce_candidates(
                right_ylabel_cand, ylabel_fall_back)

        return titles_n_labels

    def legends(self, data):
        """ Determine the legends for the graphs """
        if self.ggs['default_xscale'] == 'dat':
            return self._legends_dateplot(data)
        else:
            return self._legends_xyplot(data)

    def _legends_dateplot(self, data):
        """ Determine the legends for the dateplots """
        for dat in data['right']:
            dat['lgs']['legend'] += self.ggs['right_legend_suffix']
        return data

    def _legends_xyplot(self, data):
        """ Determine the legends for the xyplots """
        # Find the legend reg exps                                                                                                  
        reg_exp_tags = [k for k in self.ggs['legend'].keys()
                    if k.find('pattern') == 0]
        reg_exp_tags.sort()

        # Left legends
        for dat in data['left']:
            dat['lgs']['legend'] = self.ggs['legend']['default']
            for tag in reg_exp_tags:
                reg_exp = self.ggs['legend'][tag]['reg_match']
                label = dat['meta'][self.ggs['legend']['column']]
                search = re.search(reg_exp, label)
                try:
                    if len(search.group(0)) > 0:
                        dat['lgs']['legend'] =\
                            self.ggs['legend'][tag]['legend']
                except AttributeError:
                    pass

        # Right legends
        suf = self.ggs['right_legend_suffix']
        for dat in data['right']:
            dat['lgs']['legend'] = self.ggs['legend']['default'] + suf
            for tag in reg_exp_tags:
                reg_exp = self.ggs['legend'][tag]['reg_match']
                label = dat['meta'][self.ggs['legend']['column']]
                search = re.search(reg_exp, label)
                try:
                    if len(search.group(0)) > 0:
                        dat['lgs']['legend'] =\
                            self.ggs['legend'][tag]['legend'] + suf
                except AttributeError:
                    pass

        # Substitue values from meta into legends
        for dat in data['left'] + data['right']:
            for k, v in dat['meta'].items():
                if str(v) != '':
                    dat['lgs']['legend'] =\
                        dat['lgs']['legend'].replace('{' + str(k) + '}', str(v))

        return data

    def _reduce_candidates(self, candidates, fall_back):
        """ Utility function to reduce a list of candidates and a fall-back to
        one value
        """
        if len(candidates) == 0:
            return fall_back
        else:
            if [candidates[0]]*len(candidates) == candidates:
                return candidates[0]
            else:
                return fall_back        

if __name__ == "__main__":
    plot = Plot()
    plot.main()
