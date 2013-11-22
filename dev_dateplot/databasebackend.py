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

# db
import MySQLdb
from scipy import array
from scipy.interpolate import interp1d
import numpy as np
#from time import mktime
from datetime import datetime
from graphsettings import graphSettings
import re

class dataBaseBackend():
    ''' This class will fetch measurement data and measurement information from the
    database.

    Warning are numerated as such:
    warning0 = as function of
    warning1 = linscale x
    warning2 = linscale y
    warning3 = diff
    '''
    def __init__(self, options, ggs):
        # From init
        self.o = options
        self.ggs = ggs
        # A few covinience assigments
        self.type_ = options['type']
        self.from_to_dict = {'from': self.o['from_to'][0],
                             'to': self.o['from_to'][1]}
        self.plotlist = self.o['left_plotlist'] + self.o['right_plotlist']

        # Create MySQL session and cursor
        db = MySQLdb.connect(user="cinf_reader",
                             passwd="cinf_reader",
                             db="cinfdata")
        self.cursor = db.cursor()
        self.data = None

    def get_data(self):
        """ Determin plot type and return data by means of the functions:
          _get_data_dateplot
          _get_data_xyplot
        """
        self.data = {'left': [], 'right': []}
        # All of these functions manipulate self.data
        if self.ggs['default_xscale'] == 'dat':
            self._get_data_dateplot()
        else:
            self._get_data_xyplot()
        self._process_data()
        return self.data

    def _get_data_dateplot(self):
        """ Get data for date plots """
        gs = graphSettings(self.type_, params=self.from_to_dict).settings
        for side in ['left', 'right']:
            for plot_n in self.o[side + '_plotlist']:
                lgs = gs['dateplot' + str(plot_n)]
                self.data[side].append(
                    {'key': 'dateplot' + str(plot_n),
                     'lgs': lgs,
                     'data': array(self._result_from_query(lgs['query']))
                     })
        return

    def _get_data_xyplot(self):
        """ Get data for the graph plots """
        for side in ['left', 'right']:
            for plot_n in self.o[side + '_plotlist']:
                self.data[side].append(
                    self.__get_data_xyplot_single(plot_n)
                    )
        return

    def __get_data_xyplot_single(self, plot_n):
        """ This method need to be separated out from _get_data_xyplot
        because it is called manually
        """
        # lgs is the local (specific) graphsettings
        lgs = graphSettings(self.type_, params={'id': plot_n}).settings
        meta_info = self._get_meta_info(lgs)

        # Get the right query: Look if the value of the column defined
        # in graphsettings have the right value to use a special query,
        # otherwise use the default one
        query = lgs['queries']['default']
        for k, v in lgs['queries'].items():
            if k.find('extra') == 0:
                if v['match'] == meta_info[lgs['queries']['column']]:
                    query = v['query']

        ret = {'key': 'dateplot' + str(plot_n),
               'lgs': lgs,
               'meta': meta_info,
               'data': array(self._result_from_query(query))
               }
        return ret


    def _get_meta_info(self, lgs):
        # Fetch table headers from measurements table
        query = "DESCRIBE {0}".format(lgs['measurements_table'])
        self.meas_table_headers = self._result_from_query(query)
        
        # Fetch measurements table values
        query = "SELECT * FROM {0} WHERE ID = {1}".format(
            lgs['measurements_table'],
            lgs['id'])

        table_contents = self._result_from_query(query)

        # Combine table headers and values into a dictionary
        return dict(
            [[header[0], value] for header, value in
             zip(self.meas_table_headers, table_contents[0])])
        
    def _result_from_query(self, query):
        self.cursor.execute(query)
        return self.cursor.fetchall()

    def _process_data(self):
        """ Call functions to do the data processing on the data """
        # Ceate empty data structure to be used to keep track of what
        # has been done
        self.data['data_treatment'] = {
            'xlabel_addition': '',
            'y_left_label_addition': '',
            'y_right_label_addition': '',
            } 
        # Don't do data processing on date data
        if self.ggs['default_xscale'] == 'dat':
            return
        # Convert the data to be as a function of ...
        if self.o['as_function_of']:
            self._as_function_of()
        # Linearly scale x axis
        if (self.o['linscale_x0'] or
            self.o['linscale_x1'] or
            self.o['linscale_x2']):
            self._linscale_x()
        # Linearly scale y axis
        if (self.o['linscale_left_y0'] or
            self.o['linscale_left_y1'] or
            self.o['linscale_left_y2']):
            self._linscale_y('left')
        if (self.o['linscale_right_y0'] or
            self.o['linscale_right_y1'] or
            self.o['linscale_right_y2']):
            self._linscale_y('right')

         # Differentiate data on one or two axis
        if self.o['diff_left_y'] or self.o['diff_right_y']:
            self._differentiate()
        return

    ### Function that does data manipulation ###
    ############################################

    def _as_function_of(self):
        # Get the datetime of the measurement
        for dat in self.data['left'] + self.data['right']:
            query = ('SELECT {0} FROM {1} where id = {2}'
                     ''.format(self.ggs['grouping_column'],
                               self.ggs['measurements_table'],
                               dat['lgs']['id']))
            # datetime object
            datetime = self._result_from_query(query)[0][0]

            # Fetch all sets of id and label that is from the same time
            query = ('SELECT id, {0} FROM {1} WHERE TIME = \"{2}\"'
                     ''.format(self.ggs['as_function_of']['column'],
                               self.ggs['measurements_table'],
                               datetime.strftime("%Y-%m-%d %H:%M:%S")))
            measurements = self._result_from_query(query)

            # Find the one that has a label that contains the requested match
            # e.g.: "temperature"
            new_x_id = None
            for measurement in measurements:
                search = re.search(self.ggs['as_function_of']['reg_match'],
                                   measurement[1])
                try:
                    if len(search.group(0)) > 0:
                        new_x_id = measurement[0]                        
                except AttributeError:
                    pass
        
            # If there is a new x-scale (that is not None)
            if new_x_id:
                # Change the x-axis label
                self.data['data_treatment']['xlabel'] =\
                    self.ggs['as_function_of']['xlabel']
                # Fetch the pertaining temperature data
                new_x = self.__get_data_xyplot_single(new_x_id)
                """ Assumes both dat and new_x contains a common
                x-axis (typically time) and transforms the y-axis
                for new_x into the x-axis for dat
                """
                x_axis = interp1d(new_x['data'][:,0], new_x['data'][:,1])
                
                # Cut of the ends of self.data where we have no interpolation data
                start=0; end = dat['data'].shape[0]
                nx_min = new_x['data'][:,0].min()
                nx_max = new_x['data'][:,0].max()
                uncut = (start, end)
                while dat['data'][start, 0] < nx_min:
                    start += 1
                while dat['data'][end-1, 0] > nx_max:
                    end -= 1
                if (start, end) != uncut:
                    dat['data'] = dat['data'][start:end,:]

                # Transform the axis
                dat['data'][:,0] = x_axis(dat['data'][:,0])

                # Add warning about the operation to export
                dat['lgs']['warning0'] = 'As function of transformation'
        return

    def _linscale_x(self):
        """ Linearly scale the x-axis """
        scales = [k for k in self.ggs.keys() if
                  k.find('linscale_x') == 0 and self.o[k]]
        scales.sort()
        warning = 'Linear transformation of x'
        for scale in scales:
            warning += ', {0}x+{1}'.format(self.ggs[scale]['a'], self.ggs[scale]['b'])
        for scale in scales:
            a = np.float(self.ggs[scale]['a'])
            b = np.float(self.ggs[scale]['b'])
            for dat in self.data['left'] + self.data['right']:
                dat['data'][:,0] = dat['data'][:,0] * a + b
                dat['lgs']['warning1'] = warning

            self._plot_info_add_string('xlabel_addition',
                                       self.ggs[scale]['xlabel_addition'])

    def _linscale_y(self, axis):
        scales = [k for k in self.ggs.keys() if
                  (k.find('linscale_{0}_y'.format(axis)) == 0) and self.o[k]]
        scales.sort()
        warning = 'Linear transformation of y'
        for scale in scales:
            warning += ', {0}x+{1}'.format(self.ggs[scale]['a'], self.ggs[scale]['b'])
        for scale in scales:
            a = np.float(self.ggs[scale]['a'])
            b = np.float(self.ggs[scale]['b'])
            for dat in self.data[axis]:
                dat['data'][:,1] = dat['data'][:,1] * a + b
                dat['lgs']['warning2'] = warning

            self._plot_info_add_string('y_{0}_label_addition'.format(axis),
                                       self.ggs[scale]['ylabel_addition'])

    def _differentiate(self):
        """ Differentiate the data for the selected y-axis. This
        method assumes a constant step size between x-values and hence
        will produce wrong results if that criteria is not met
        """
        for side in ['left', 'right']:
            if self.o['diff_{0}_y'.format(side)]:
                for dat in self.data[side]:
                    x_differences = np.diff(dat['data'][:,0])
                    x_step_av = np.sum(x_differences)/len(x_differences)
                    dat['data'][:,1] = np.gradient(dat['data'][:,1], x_step_av)
                    dat['lgs']['warning3'] = "Differentiation of y"
                    
                self._plot_info_add_string(
                    'y_{0}_label_addition'.format(side),
                    self.ggs['diff_{0}_y'.format(side)]['ylabel_addition']
                    )


    def _plot_info_add_string(self, key, string):
        if self.data['data_treatment'][key] == '':
            self.data['data_treatment'][key] = string
        else:
            self.data['data_treatment'][key] += ',' + string
