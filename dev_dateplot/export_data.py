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

import sys
sys.path.insert(1, '/var/www/cinfdata')
from optparse import OptionParser
from databasebackend import dataBaseBackend
from graphsettings import graphSettings
from time import strptime, strftime, time, localtime

class ExportData:
    """ Class for data export """

    def __init__(self):
        """ Turn all the input options into appropriate python data
        structures and initiate a few helper objects
        """
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
        for bound in ['xscale_bounding', 'left_yscale_bounding',
                      'right_yscale_bounding']:
            if options.__dict__[bound] not in ['', '0,0', ',']:
                self.o[bound] = tuple(
                    [float(b) for b in options.__dict__[bound].split(',')]
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
        for key in ['type']:
            self.o[key] = options.__dict__[key]
        # From_to
        self.o['from_to'] = options.from_to.split(',')
        ### Done processing options

        # If a dateplot and called without (valid) datetimes fill them in
        try:
            strptime(self.o['from_to'][0], '%Y-%m-%d  %H:%M')
            strptime(self.o['from_to'][1], '%Y-%m-%d  %H:%M')
        except ValueError:
            # [now-1d, now]
            self.o['from_to'][0] = strftime('%Y-%m-%d  %H:%M',
                                            localtime(time()-24*3600))
            self.o['from_to'][1] = strftime('%Y-%m-%d  %H:%M')

        # Get a (g)eneral (g)raph (s)ettings object
        # (Is not polulated with data set specific values) 
        self.ggs = graphSettings(self.o['type']).settings

        ### Create database backend object
        self.db = dataBaseBackend(options=self.o, ggs=self.ggs)

        self.defaults = {}

    def export(self):
        """ To form the export:
        1) Fetch the data
        2) Print out the type specific header
        3) Print out the data
        """
        self.data = self.db.get_data()
        # No data -> we are done
        if len(self.data['left'] + self.data['right']) == 0:
            print "No data"
            return
        if self.ggs['default_xscale'] == 'dat':
            self._print_out_header_date_data(self.data)
        else:
            self._print_out_header_xy_data(self.data)
        self._print_out_data(self.data)

    def _print_out_header_xy_data(self, data):
        """ Prints out the header. This includes the mandatory fields, the user
        defined fields and warnings if the data has been manipulated
        """
        ### Find header fields to output
        # Add the four mandatory header fields (lists [field,name])        
        mandatory_fields = self.ggs['mandatory_export_fields'].keys()
        mandatory_fields.sort()
        header_fields = []
        for field in mandatory_fields:
            header_fields.append([
                    self.ggs['mandatory_export_fields'][field]['field'],
                    self.ggs['mandatory_export_fields'][field]['name']
                    ])

        # Add the user defined header fields for this type of graph
        if self.ggs.has_key('parameters'):
            keys = self.ggs['parameters'].keys()
            keys.sort()
            for key in keys:
                header_fields.append([
                        self.ggs['parameters'][key]['field'],
                        self.ggs['parameters'][key]['name']
                        ])

        # Produce header
        for key, value in header_fields:
            out = []
            for n, dat in enumerate(data['left'] + data['right']):
                out += ['"' + value + '"', '"' + str(dat['meta'][key]) + '"']
            print('\t'.join(out))
    
        ### Find warnings if the data has been manipulated (treated)
        # Find warnings in the graphsettings dict
        warning_keys = []
        for dat in (data['left'] + data['right']):
            for key in ['warning' + str(n) for n in range(4)]:
                # If the current warning key is found in graphsettings and we
                # don't already have it in warning_keys, add it
                if (dat['lgs'].has_key(key) and (warning_keys.count(key)) == 0):
                    warning_keys.append(key)

        # Produce warnings
        for warning in warning_keys:
            out = []
            for n, d in enumerate(data['left'] + data['right']):
                # Only make output if the current warning key is present in the
                # current datasets graphsettings
                if d['lgs'].has_key(warning):
                    out += ['"' + warning + '"', '"' + d['lgs'][warning] + '"']
                else:
                    out += ['', '']
            print('\t'.join(out))
        
        # We always produce 20 lines of header or more
        n_header_lines = len(header_fields) + len(warning_keys)
        for n in range(20-n_header_lines):
            print('')
    
        # Produce the data header from the label column
        label_column = self.ggs['label_column']
        out = []
        for n, dat in enumerate(data['left'] + data['right']):
            basename = str(dat['meta'][label_column])
            out += [basename + '-x', basename + '-y']
        print('\t'.join(out))

    def _print_out_header_date_data(self, data):
        """ Prints out the header for date data. This consist purely
        of the 'Datetime' for the x's and the legends for y's
        """
        legends = []
        for dat in (data['left'] + data['right']):
            legends += ['Datetime', dat['lgs']['legend']]
        print('\t'.join(legends))

    def _print_out_data(self, data):
        """ Method that prints out the data part of the export """
        # Find the largest number of measurements in the different datasets
        n_measure = max([dat['data'].shape[0]
                         for dat in (data['left'] + data['right'])])
        
        # Produce the data output (loop n though values from 0 to n_measure)
        for n in range(n_measure):
            out = []
            for m, dat in enumerate(data['left'] + data['right']):
                # Since not all datasets are same size, check here whether the
                # current dataset actually has as many values as the current n,
                # if it does not simply output separators (IMPORTANT)
                if n < dat['data'].shape[0]:
                    out += [str(dat['data'][n,0]), str(dat['data'][n,1])]
                else:
                    out += ['', '']
            print('\t'.join(out))

if __name__ == '__main__':
    ED = ExportData()
    ED.export()
