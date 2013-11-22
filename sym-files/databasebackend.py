#!/usr/bin/python

"""
Copyright (C) 2012 Robert Jensen, Thomas Anderser and Kenneth Nielsen

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
#from time import mktime
from datetime import datetime, timedelta
from graphsettings import graphSettings

class dataBaseBackend():
    ''' Class dataBaseBackend
    This class will fetch measurement data and measurement information from the
    database.
    '''
    def __init__(self, typed, id_list=[], from_to={'from':None, 'to':None},
                 transform_x=None, flip_x=None, change_t_scale=None,
                 offsets={}, as_function_of_t=False,
                 shift_temp_unit=False, shift_be_ke=False):
        # From init
        self.typed = typed
        self.transform_x = transform_x
        self.flip_x = flip_x
        self.change_t_scale = change_t_scale
        self.offsets = offsets
        self.global_settings = None
        self.from_to = from_to
        self.as_function_of_t = as_function_of_t
        self.shift_temp_unit = shift_temp_unit
        self.shift_be_ke = shift_be_ke
        self.c_to_k = 273.15
        if len(id_list) == 0:
            self.id_list = [None]
        else:
            self.id_list = id_list
            
        # Fetch the graphsettings
        self.params = from_to.copy()
        self.params['id'] = self.id_list[0]
        self.global_settings = graphSettings(self.typed,
                                             params=self.params).settings

        # For dateplots, fill in "from" "to" values if to few are given. If
        # missing "to" becomes now and "from" becomes 1 day ago
        if self.global_settings['default_xscale'] == 'dat':
            if not self.from_to['from']:
                self.from_to['from'] = (datetime.now()-timedelta(days=1)).\
                    strftime('%Y-%m-%d %H:%M')
            if not self.from_to['to']:
                self.from_to['to'] = datetime.now().strftime('%Y-%m-%d %H:%M')

        # Added here to make sure that the updated from to values gets taken
        # into account in the hash in plot.py
        self.global_settings['from_to'] = self.from_to

        # Create MySQL session and cursor
        db = MySQLdb.connect(user="cinf_reader", passwd="cinf_reader",
                             db="cinfdata")
        self.cursor = db.cursor()

    def get_data_count(self):
        """ get data """
        out = []
        # If it is a dateplot
        if self.global_settings['default_xscale'] == 'dat':
            self.params = self.from_to.copy()
            self.params['id'] = self.id_list[0]
            self.gs = graphSettings(self.typed, params=self.params).settings
            new_key = key = 'query'
            counter = 0
            while self.gs.has_key(new_key):
                # h[h.lower().find('from'):]
                query = self.gs[new_key]
                query = 'select count(*) ' + query[query.lower().find('from'):]
                out.append(self._result_from_query(query)[0][0])
                counter += 1
                new_key = key + str(counter)
        else:
            for idl in self.id_list:
                # Reset return varaibles
                self.data = None; self.gs = None; self.info = {}
                self.params = self.from_to.copy()
                self.params['id'] = idl
                self.gs = graphSettings(self.typed, params=self.params).settings
                query = self.gs['query']
                query = 'select count(*) ' + query[query.lower().find('from'):]
                out.append(self._result_from_query(query)[0][0])

        return out


    def get_data(self):   
        """ get data """
        # If it is a dateplot
        if self.global_settings['default_xscale'] == 'dat':
            self.params = self.from_to.copy()
            self.params['id'] = self.id_list[0]
            self.gs = graphSettings(self.typed, params=self.params).settings
            new_key = key = 'query'
            counter = 0
            while self.gs.has_key(new_key):
                self.data = None; self.info = {}
                self.data = array(self._result_from_query(self.gs[new_key]))
                if self.gs.has_key('ordering'):
                    (location, color) = self.gs['ordering'].split(',')[counter].split('|')
                    self.info['on_the_right'] = True if location == 'right' else False
                    self.info['color'] = color
                else:
                    self.info['on_the_right'] = False
                    
                yield {'data':self.data, 'gs':self.gs, 'info':self.info}
                counter += 1
                new_key = key + str(counter)
        else:
            for idl in self.id_list:
                # Reset return varaibles
                self.data = None; self.gs = None; self.info = {}
                self.params = self.from_to.copy()
                self.params['id'] = idl
                self.gs = graphSettings(self.typed, params=self.params).settings
                self.data = array(self._result_from_query(self.gs['query']))
                self.info = self._get_info()

                # Determine if it should go on the right y axis
                if self.gs.has_key('right_y_axis_field_value'):
                    names_on_the_right = [element.lower().strip() for element
                                          in self.gs['right_y_axis_field_value'].split(',')]
                    if self.info[self.gs['right_y_axis_field_name']].lower() in names_on_the_right:
                        self.info['on_the_right'] = True
                    else:
                        self.info['on_the_right'] = False
                else:
                    self.info['on_the_right'] = False
                
                # process_data() acts on self.data
                self._process_data(idl)
                yield {'data':self.data, 'gs':self.gs, 'info': self.info}

    def _get_info(self):
        # Fetch table headers from measurements table
        query = "DESCRIBE {0}".format(self.gs['measurements_table'])
        self.meas_table_headers = self._result_from_query(query)
        
        #for n, datum in enumerate(self.data):
        # Fetch measurements table values
        query = "SELECT * FROM {0} WHERE ID = {1}".format(
            self.gs['measurements_table'],
            self.gs['id'])

        table_contents = self._result_from_query(query)

        # Combine table headers and values into a dictionary
        return dict(
            [[header[0], value] for header, value in
             zip(self.meas_table_headers, table_contents[0])])
        
    def _result_from_query(self, query):
        self.cursor.execute(query)
        return self.cursor.fetchall()

    def _process_data(self, idl):
        # Here we do data manipulation if requested
        if self.typed == "massspectrum":
            # This function acts on the self.data variable
            self._displace_negative(idl)
        elif self.typed == "xps":
            # Shift between BE and KE if requested
            if self.shift_be_ke:
                self._shift_be_ke()
            #excitation_energy
            pass
        elif self.typed == "masstime":
            # Shift x from time to temperature is requested
            if self.as_function_of_t:
                self._as_function_of_t(idl)
                if self.shift_temp_unit:
                    self._shift_temp_unit()
    
    ### Function that does data manipulation ###

    def _displace_negative(self, idl):
        # Apply the offset provided from php to the values
        if self.offsets[idl] != '0':
            self.data[:,1] = self.data[:,1] + float(self.offsets[idl])
            # Add warning about the operation to export
            self.gs['warning0'] = ('WARNING: ' + str(self.offsets[idl]) +
                                   ' have been added to all values to avoid '
                                   'unplottable values')
            

    def _as_function_of_t(self, idl):
        # Get the datetime of the measurement
        query = ('SELECT time FROM {0} where id = {1}'
                 ''.format(self.gs['measurements_table'], idl))
        # datetime object
        datetime = self._result_from_query(query)[0][0]

        # Fetch all sets of id and label that is from the same time
        query = ('SELECT id, mass_label FROM {0} WHERE TIME = \"{1}\"'
                 ''.format(self.gs['measurements_table'],
                           datetime.strftime("%Y-%m-%d %H:%M:%S")))
        measurements = self._result_from_query(query)

        # Find the one that has a label that contains "temperature"
        temperature_id=None
        for measurement in measurements:
            if measurement[1].lower().count('temperature') > 0:
                temperature_id = measurement[0]
        
        # If there is a temperature (that is not None)
        if temperature_id:
            # Fetch the pertaining temperature data
            query = self.gs['t_query'].format(t_id=temperature_id)
            temperature_data = array(self._result_from_query(query))
            """ Assumes both self.data (mass) and temperature_data
            (temperature) contains a common x-axis (typical time) and
            transforms the y-axis temperature_data into the x-axis of the
            self.data
            """
            x_axis = interp1d(temperature_data[:,0],temperature_data[:,1])
            
            # Cut of the ends of self.data where we have no interpolation data
            start=0; end=self.data.shape[0]
            ttmin = temperature_data[:,0].min()
            ttmax = temperature_data[:,0].max()
            uncut = (start, end)
            while self.data[start,0] < ttmin:
                start += 1
            while self.data[end-1,0] > ttmax:
                end -= 1
            if (start, end) != uncut:
                self.data = self.data[start:end,:]

            # Transform the axis
            self.data[:,0] = x_axis(self.data[:,0])

        # Add warning about the operation to export
        self.gs['warning1'] = ('WARNING: This data has been transformed to '
                               'contain values as a function of temperature')

    def _shift_temp_unit(self):
        if self.gs['temperature_unit'] == 'C':
            self.data[:,0] = self.data[:,0] + self.c_to_k
        elif self.gs['temperature_unit'] == 'K':
            self.data[:,0] = self.data[:,0] - self.c_to_k
            
        # Add warning about the operation to export
        self.gs['warning2'] = ('WARNING: The temperature data in this dataset '
                               'has had its unit shifted')

    def _shift_be_ke(self):
        # Get the excitation energy from the info (from db)
        if self.info.has_key('excitation_energy'):
            try:
                ee = float(self.info['excitation_energy'])
            except TypeError:
                raise SystemExit('In order to shift between BE and KE '
                                 'the \"excitation_energy\" field in your '
                                 'measurements table must be filled in\n\n'
                                 'Exiting')
        else:
            raise SystemExit('In order to shift between BE and KE your '
                             'measurements table must contain a '
                             '\"excitation_energy\" field.\n\nExiting')
        # Get the info about whether be or ke has been saved in db
        if self.gs.has_key('in_db'):
            in_db = self.gs['in_db']
        else:
            raise SystemExit('In order to shift between BE and KE you must '
                             'must fill in the \"in_db\" field with be or ke '
                             'in graphsettings to tell the system which has '
                             'been saved to the database.\n\nExiting')
        # Shift from be to ke
        if in_db == 'be' or in_db == 'ke':
            # KE = ee - BE   or   BE = ee - KE
            self.data[:,0] = ee - self.data[:,0]
            self.gs['warning3'] = ('WARNING: The energy data in this dataset '
                                   'has been shifted from ' + in_db)
        else:
            raise SystemExit('In order to shift between BE and KE you must '
                             'must fill in the \"in_db\" field with be or ke '
                             'in graphsettings to tell the system which has '
                             'been saved to the database.\n\nExiting')
