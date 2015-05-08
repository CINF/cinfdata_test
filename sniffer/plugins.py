import numpy


class MassSpectraOffset(object):
    """Plugin that will offset mass spectra by -1.1 times the minimum y-value
    found in all of the spectra to prevent errors when plotting on log scale
    """

    def __init__(self, settings, plot_options, ggs=None):
        self.settings = settings
        self.label_additions = {
            'xlabel_addition': '',
            'y_left_label_addition': '',
            'y_right_label_addition': '',
        }

    def run(self, left, right):
        """The main run method"""
        # Find minimum value
        min_ = 1
        for dat in left + right:
            min_ = min(numpy.min(dat['data'][:, 1]), min_)

        # If we need to correct
        if min_ <= 0:
            # Shift the data
            shift = min_ * -1.1
            for dat in left + right:
                dat['data'][:, 1] += shift

            # Print the offset and change the axis labels
            print 'All spectra offset by: {0}'.format(shift)
            message = 'Offset by: {0:.2e}'.format(shift)
        else:
            print 'No offset was necessary, min value was: {0}'.format(min_)
            message = 'No offset'

        self.label_additions['y_left_label_addition'] = message
        self.label_additions['y_right_label_addition'] = message

        return self.label_additions
