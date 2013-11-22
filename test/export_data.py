#!/usr/bin/python

import sys
sys.path.insert(1, '/var/www/cinfdata')
from optparse import OptionParser
from databasebackend import dataBaseBackend

class ExportData:
    """ Class for data export """
    def __init__(self):
        """ 1) Inititialise parser options.
        2) Proces options (e.g. turn string lists into pyton lists)
        3) Create dataBaseBackend instance for retrievel of data and info
        4) Initialise variables
        """

        ### Parse options and arguments
        # Create option parser
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
        parser.add_option("-j", "--shift_be_ke")
        # Parse the options and arguments
        (self.options, args) = parser.parse_args()

        ### Process options
        # Fetch idlist
        self.idlist = [int(element) for element in 
                       self.options.idlist.split(',')[1:]]
        # Turn the offset "key:value," pair string into a dictionary
        self.offsets =  dict([[int(offset.split(':')[0]),
                               offset.split(':')[1]] for offset in 
                              self.options.offset.split(',')[1:]])
        # Turn booleans into python booleans
        self.as_function_of_t = True if self.options.as_function_of_t ==\
            'checked' else False
        self.shift_temp_unit = True if self.options.shift_temp_unit ==\
            'checked' else False
        self.logscale = True if self.options.logscale == 'checked' else False
        self.shift_be_ke = True if self.options.shift_be_ke ==\
            'checked' else False

        ### Create dataBaseBackend instance for data and info retrievel
        self.from_to = {'from':self.options.from_d, 'to':self.options.to_d}
        self.db = dataBaseBackend(typed=self.options.type, from_to=self.from_to,
                                  id_list=self.idlist, offsets=self.offsets,
                                  as_function_of_t=self.as_function_of_t,
                                  shift_temp_unit=self.shift_temp_unit,
                                  shift_be_ke=self.shift_be_ke)

        ### Initialise the local data variable to the empty list
        self.data = []
        
    def _get_data(self):
        """ Method that gets the data from the db backend """
        # Get data (db.get_data() is an iterator, calling list on it gets us
        # all the values at once)
        return list(self.db.get_data())

    def export(self):
        """ Method that makes the export (prints the output)
        
        This function calls three internal methods sequentially to do the work:
        _get_data()   Gets the data from the dataBaseBackend object and returns
                      it
        _print_out_header(self.data)   Prints out the header. This includes the
                      mandatory fields, the user defind fields and warnings if
                      the data has been manipulated
        _print_out_data(..)   Prints out the data
        """
        self.data = self._get_data()
        if len(self.data) == 0:
            print 'No data was fetched from the database'
            return
        
        self._print_out_header(self.data)
        self._print_out_data(self.data)
        return

    def _print_out_header(self, data):
        """ Prints out the header. This includes the mandatory fields, the user
        defind fields and warnings if the data has been manipulated
        """
        ### Find header fields to output
        # Work around differing name for comment column in database
        comment_field_name = ('comment' if data[0]['info'].has_key('comment')
                              else 'Comment')
        
        # Add the four mandatory header fields
        header_fields = [{'field':'id', 'name':'Id'},
                         {'field':'type', 'name':'Type'},
                         {'field':'time', 'name':'Recorded at'},
                         {'field':comment_field_name, 'name':'Comment'}]

        # Add the user defined header fields for this type of graph
        for n in range(16):
            field = 'param' + str(n) + '_field'
            name = 'param' + str(n) + '_name'
            if data[0]['gs'].has_key(field) and data[0]['gs'].has_key(field):
                header_fields.append({'field':data[0]['gs'][field],
                                      'name':data[0]['gs'][name]})

        # Produce header
        for key in header_fields:
            out = ""
            for n, d in enumerate(data):
                # Give an error if the field name specified in graphsettings.xml
                # for the user defined filds does not exist
                try:
                    value = str(d['info'][key['field']])
                except KeyError:
                    print ('\nERROR: The parameter field \"' + key['field'] +
                           '" you have specified in your graphsettings.xml '
                           'does not exist in the database. Valid value in '
                           'the database are: ' + str(d['info'].keys())) + '\n'
                if n == 0:
                    out += ("\"" + key['name'] + "\"" + "\t\"" +
                            value + "\"")
                else:
                    out += ("\t\"" + key['name'] + "\"" + "\t\"" +
                            value + "\"")
            print(out)
    
        ### Find warnings if the data has been manipulated
        # Find warnings in the graphsettings dict
        warning_keys = []
        for datum in data:
            for n in range(16):
                # If the current warning key is found in graphsettings and we
                # don't already have it in warning_keys, add it
                if (datum['gs'].has_key('warning' + str(n)) and
                    (warning_keys.count('warning' + str(n))) == 0):
                    warning_keys.append('warning' + str(n))

        # Produce warnings
        for warning in warning_keys:
            out = ""
            for n, d in enumerate(data):
                # Only make output if the current warning key is present in the
                # current datasets graphsettings
                if d['gs'].has_key(warning):
                    w_name = warning
                    w_value = d['gs'][warning]
                else:
                    w_name = ''
                    w_value = ''
                if n == 0:
                    out += ("\"" + w_name + "\"" + "\t\"" +
                            w_value + "\"")
                else:
                    out += ("\t\"" + w_name + "\"" + "\t\"" +
                            w_value + "\"")
            print(out)
        
        # We always produce exactly 20 lines of header
        n_header_lines = len(header_fields) + len(warning_keys)
        for n in range(20-n_header_lines):
            print
    
        # Produce the data header
        out = ''
        for n, datum in enumerate(data):
            # Use the mass label if it is there, else use id
            if datum['info'].has_key('mass_label') and\
                    datum['info']['mass_label']:
                basename = datum['info']['mass_label']
            else:
                basename = datum['gs']['id']
            if n == 0:
                out += basename + '-x\t' + basename + '-y'
            else:
                out += '\t' + basename + '-x\t' + basename + '-y'
        print(out)    


    def _print_out_data(self, data):
        """ Method that prints out the data part of the export """
        # Find the largest number of measurements in the different datasets
        n_measure = max([datum['data'].shape[0] for datum in data])
        
        # Produce the data output (loop n though values from 0 to n_measure)
        for n in range(n_measure):
            out=""
            for m in range(len(data)):
                # Since not all datasets are same size, check here whether the
                # current dataset actually has as many values as the current n,
                # if it does not simply output seperators (important)
                if n < data[m]['data'].shape[0]:
                    if m == 0:
                        out += str(data[m]['data'][n,0]) + "\t" +\
                            str(data[m]['data'][n,1])
                    else:
                        out += "\t" + str(data[m]['data'][n,0]) + "\t" +\
                            str(data[m]['data'][n,1]) 
                else:
                    if m == 0:
                        out+="\t"
                    else:
                        out+="\t\t" 
            print out

if __name__ == "__main__":
    ed = ExportData()
    ed.export()
