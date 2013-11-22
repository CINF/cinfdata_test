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

import xml.etree.ElementTree

class graphSettings(object):
    """ This class parses the graphsettings from the graphsettings.xml file and
    returns them as the internal settings variables.
    """
    def __init__(self, typed=None, params={}, filepath='graphsettings.xml'):
        """ Initialise:

        Parameters:
        typed(str)      The type of graph, can be either: pressure, temperature,
                        morning_pressure, iv, xps, iss, masstime or massspectrum
        params(dict)    The parameters to be inserted in the settings
        """

        self.params = params
        # Create settings dictionary and input type and maybe id
        self.settings = {'typed':typed}
        if params.has_key('id') and params['id'] != '':
            self.settings['id'] = str(params['id'])


        gs = xml.etree.ElementTree.ElementTree()
        gs.parse(filepath)
        
        # Update with global settings
        global_settings = gs.find('global_settings')
        self._update_settings_with_xml(global_settings)

        # Update with graph settings
        if typed is not None:
            graph = [e for e in gs.findall('graph') if e.attrib['type'] == typed][0]
            self._update_settings_with_xml(graph)

    def _update_settings_with_xml(self, xml):
        """ Update the settings dictionary with the dictionary returned from
        xml_tree_to_associative_array
        """
        self.settings.update(self._xml_tree_to_assiciative_dictionary(xml))

    def _xml_tree_to_assiciative_dictionary(self, xml):
        """ Recursively turn xml tree into a \"dictionary tree\" """
        ret = {}
        for element in list(xml):
            if len(list(element)) == 0:
                ret[element.tag] = self._substitute_variables(element.text)
            else:
                ret[element.tag] = self._xml_tree_to_assiciative_dictionary(element)
        return ret

    def _substitute_variables(self, text):
        """ Substitute the {var} type variables in certain graphsettings
        entries with values from param
        """
        for key in self.params.keys():
            if self.params[key] != '':
                text = text.replace('{' + key + '}', str(self.params[key]))
        return text
    
    def get_settings(self):
        """ Return the settings dictionary """
        return self.settings
