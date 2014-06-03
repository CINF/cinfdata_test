import numpy


class Switch(object):
    def __init__(self, settings):
        self.label_additions = {
            'xlabel_addition': '',
            'y_left_label_addition': '',
            'y_right_label_addition': '',
        }

    def run(self, left, right):
        for dat in left:
            tmp = numpy.copy(dat['data'][:, 1])
            dat['data'][:, 1] = dat['data'][:, 0]
            dat['data'][:, 0] = tmp
        self.label_additions['y_left_label_addition'] = 'Switch'
        print """
        <table style="width:300px">
          <tr>
            <td>Jill</td>
            <td>Smith</td> 
            <td>50</td>
          </tr>
          <tr>
            <td>Eve</td>
            <td>Jackson</td> 
            <td>94</td>
          </tr>
        </table>
        """
        return self.label_additions

class Factor(object):
    def __init__(self, settings):
        self.settings = settings
        self.label_additions = {
            'xlabel_addition': '',
            'y_left_label_addition': '',
            'y_right_label_addition': '',
        }

    def run(self, left, right):
        factor = int(self.settings['input'])
        for dat in left:
            dat['data'][:, 1] = dat['data'][:, 1] * factor
        
        self.label_additions['y_left_label_addition'] = \
            'x{0}'.format(factor)
        print 'Multiplied &<> something with a factor of {0}'.format(factor)
        print 'Fixed width goodness'
        return self.label_additions
