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

from xml.dom import minidom

class graphSettings:
    """ This class parses the graphsettings from the graphsettings.xml file and
    returns them as the internal settings variables.
    """
    def __init__(self, typed, params={}):
        """ Initialise:

        Parameters:
        typed(str)      The type of graph, can be either: pressure, temperature,
                        morning_pressure, iv, xps, iss, masstime or massspectrum
        params(dict)    The parameters to be inserted in the settings
        """
        # initiatize rcparams
        rcparams_regular = {}
        rcparams_small = {}
        elements = None
        
        gs = minidom.parse('graphsettings.xml')
        self.settings = {'typed':typed}
        if params['id']:
            self.settings['id'] = str(params['id'])

        # Find the right 'graph' node and get its sub-nodes
        for graph in gs.getElementsByTagName('graph'):
            if graph.attributes['type'].value == typed:
                elements = graph.childNodes
        
        # Check that we actually have the type of graph in graphsettings.xml
        if elements is None:
            raise SystemExit('The specified type: \"' + str(typed) + '\" does '
                             'not exist in you graphsettins.xml.\n\n'
                             'Exiting')
        
        # Find the nodes that contain settings data (should be more elegantly)
        # and save it to settings 
        for element in elements:
            if element.nodeName not in ["#text", "#comment"]:
                self.settings[element.nodeName] = element.childNodes[0].data

        # Find the nodes that contain global_settings data (should be more
        # elegantly) and save it to settings 
        for global_setting in gs.getElementsByTagName('global_settings')[0].\
                childNodes:
            if global_setting.nodeName not in ["#text", "#comment"]:
                self.settings[global_setting.nodeName] =\
                    global_setting.childNodes[0].data
        
        # Replace keys in the entries in graphsettings.xml with values from
        # params
        for s_key in self.settings.keys():
            for p_key in params.keys():
                if params[p_key]:
                    #print self.settings[s_key], '{' + p_key + '}', params[p_key]
                    self.settings[s_key] = self.settings[s_key].replace(
                        '{' + p_key + '}', str(params[p_key]))
        
        # Take the strings that describe regular and small figure settings and
        # turn them into dictionaries
        if self.settings.has_key('regular_fig_settings'):
            rcparams_regular = dict([[item.split(':')[0], item.split(':')[1]]
                                     for item in
                                     self.settings['regular_fig_settings']
                                     .split('|')])
        if self.settings.has_key('small_fig_settings'):
            rcparams_small = dict([[item.split(':')[0], item.split(':')[1]]
                                   for item in
                                   self.settings['small_fig_settings']
                                   .split('|')])

        ## Some entries in the settings needs to be converted from strings into
        ## other types
        # Convert lists in strings into lists, i.e. '[3,4]' to [3,4]
        for sett in [rcparams_small, rcparams_regular]:
            for key in sett.keys():
                # There are the keys that correspond to lists of floats
                if key in ['figure.figsize']:
                    sett[key] = [float(element) for element in 
                                 sett[key].strip('[]').split(',')]
                # There are keys that contains floats, that are not converted internally
                if key in ['lines.linewidth', 'axes.linewidth']:
                    sett[key] = float(sett[key])
            
        self.settings['rcparams_regular'] = rcparams_regular
        self.settings['rcparams_small'] = rcparams_small

        
if __name__ == "__main__":
    print ('########## Testing graphsettings.py ##########\n'
           '### masstime req_is=42')
    print graphSettings('masstime', req_id=42).settings, '\n'
    print '### masstime from_to={\'from\':\'today\', \'to\':\'tomorrow\'}'
    print graphSettings('temperature', from_to={'from':'today', 'to':'tomorrow'}).settings, '\n'
