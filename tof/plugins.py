import numpy
import sys
sys.path.append('flighttime')
import tof_model as tm
import tof_helpers 

class Massscale(object):
    def __init__(self, settings):
        self.settings = settings
        self.label_additions = {
            'xlabel_addition': '',
            'y_left_label_addition': '',
            'y_right_label_addition': '',
        }

    def run(self, left, right):
        for dat in left:
            if dat['meta']['tof_R1_voltage'] == 0:
                dat['data'][:, 1] = 0
            else:
                tm.Voltages = {} # Contains all electrical values of the maching
                tm.Voltages['pulse'] = dat['meta']['tof_pulse_voltage']
                tm.Voltages['liner'] = dat['meta']['tof_liner_voltage']
                tm.Voltages['R1'] = dat['meta']['tof_R1_voltage']
                tm.Voltages['R2'] = dat['meta']['tof_R2_voltage']
                coeff = tof_helpers.extrapolate()
                print coeff
                dat['data'][:, 0] = ((dat['data'][:, 0]-0.31)/coeff[0])**(1/coeff[1])

        self.label_additions['xlabel_addition'] = \
            'Converted to mass'
        return self.label_additions
