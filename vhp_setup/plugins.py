

from __future__ import print_function, division
from pprint import pprint
import MySQLdb
import numpy
from scipy import interpolate


class NException(Exception):
    """A normalization exception"""
    pass


class Normalizer(object):
    """Normalizer class

    This class will normalize all the measurements on the left y-axis. Each of
    the data sets will be normalized to the the measurement with the label
    given by input, that is from the same measurement set as the data that is
    to be normalized"""

    def __init__(self, settings, plot_options, ggs=None):
        self.settings = settings
        self.label_additions = {
            'xlabel_addition': '',
            'y_left_label_addition': '',
            'y_right_label_addition': '',
        }
        con = MySQLdb.connect(host='servcinf', user='cinf_reader',
                              passwd='cinf_reader', db='cinfdata')
        self.cursor = con.cursor()

        # Cache for interpolation functions
        self.interpol_func_cache = {}

        # We need the left and right data series outside self.run
        self.left = None
        self.right = None

    def run(self, left, right):
        """Normalizes each of the data sets on the left y-axis"""
        self.left = left
        self.right = right

        for dat in left:
            # Try to normalize and if it fails, in an acceptable manner, print
            # out the exception message
            try:
                self.normalize(dat)
            except NException as exception:
                print(exception)

        return self.label_additions

    def normalize(self, dat):
        """Normalizes a single data set

        Args:
            dat (dict): A data set dict, contains both data and metadata

        Raises:
            NException: On normalization errors
        """

        # Get the id for the data set to normalize to from the measurements
        # table, i.e. the one with the same value for grouping column and the
        # entered label
        query = ('select id, {1} from {0} where {1} = %s and {2} = %s')
        # grouping_column and label_column is retrieved from lgs = local graph
        # settings
        query = query.format(
            dat['lgs']['measurements_table'],
            dat['lgs']['grouping_column'],
            dat['lgs']['label_column'],
            )

        query_args = (dat['meta'][dat['lgs']['grouping_column']],
                      self.settings['input'])
        self.cursor.execute(query, query_args)

        # Fetch the id. If there is none, most likely because we entered a bad
        # label, raise an exception
        try:
            normalize_to_id, normalize_grouping = self.cursor.fetchall()[0]
        except (IndexError, ValueError):
            message = 'Unable to normalize {0} ({1}, {2}) to "{3}"'
            message = message.format(
                dat['meta']['id'],
                dat['meta']['mass_label'],
                dat['meta'][dat['lgs']['grouping_column']],
                self.settings['input']
                )
            raise NException(message)

        # Get the normalization function
        normalize_function = self.get_normalize_function(normalize_to_id, dat,
                                                         normalize_grouping)

        # Create a normalized dataset and replace the original one NOTE: Uses
        # __future__ division i.e. float division
        out = numpy.zeros(dat['data'].shape)
        out[:, 0] = dat['data'][:, 0]
        out[:, 1] = dat['data'][:, 1] / normalize_function(out[:, 0])
        dat['data'] = out

        # Success message
        message = 'Normalized {0} ({1}, {2}) to {3} ({4}, {5})'
        message = message.format(
                dat['meta']['id'],
                dat['meta']['mass_label'],
                dat['meta'][dat['lgs']['grouping_column']],
                normalize_to_id,
                self.settings['input'],
                normalize_grouping,
              )
        print(message)

        # Let the user know, the data is modified
        self.label_additions['y_left_label_addition'] = 'Normalized data, A.u.'

    def get_normalize_function(self, id_, dat, normalize_grouping):
        """Returns the normalization function

        The normalization function is the function created by linear
        interpolation between all the points in the data set that the data is
        normalized to
        """
        # If cached, return the cached function
        if id_ in self.interpol_func_cache:
            return self.interpol_func_cache[id_]

        # If not cached, get the data to normalize to, either from the data
        # sets in left right data, if it is there ...
        data = None
        for data_ in self.left + self.right:
            if data_['meta']['id'] == id_:
                data = data_['data']

        # ... or from the db
        if data is None:
            # NOTE. For measurement types where there are more than one query,
            # this will need to be replaced with code that picks the correct
            # query, see the databasebackend for an example of this algorithm
            query = self.ggs['queries']['default']
            query = query.format(id=id_)
            self.cursor.execute(query)
            data = numpy.array(self.cursor.fetchall())

        # If there is too little data
        if data.shape[0] < 2:
            message = 'Unable to normalize {0} ({1}, {2}), to little data in '\
                '{3} ({4}, {5})'
            message = message.format(
                dat['meta']['id'],
                dat['meta']['mass_label'],
                dat['meta'][dat['lgs']['grouping_column']],
                id_,
                self.settings['input'],
                normalize_grouping,
                )
            raise NException(message)

        # Make the interpolation function. With bounds_error=False, and no
        # fill_value given, values outside the interpolation range will be
        # filled in with NaN's
        self.interpol_func_cache[id_] = interpolate.interp1d(
            data[:, 0], data[:, 1],
            kind='linear', bounds_error=False
            )

        return self.interpol_func_cache[id_]
