from scipy import signal
import numpy
import sys
sys.path.append('flighttime')
import tof_model as tm
import tof_helpers 
import time

class Normalize(object):
    def __init__(self, settings, plot_options, ggs=None):
        self.settings = settings
        self.label_additions = {
            'xlabel_addition': '',
            'y_left_label_addition': '',
            'y_right_label_addition': '',
        }

    def run(self, left, right):
        for dat in left + right:
            if max(dat['data'][:, 1]) > 5: # This is a count spectrum
                dat['data'][:, 1] = dat['data'][:, 1] / dat['meta']['tof_iterations']
                print('Number of iterations: ' + str(dat['meta']['tof_iterations']))
        self.label_additions['ylabel_addition'] = \
            'Normalized to iterations'
        return self.label_additions


class Smooth(object):
    def __init__(self, settings, plot_options, ggs=None):
        self.settings = settings
        self.label_additions = {
            'xlabel_addition': '',
            'y_left_label_addition': '',
            'y_right_label_addition': '',
        }

    def run(self, left, right):
        try:
            n = int(self.settings['input'])
        except ValueError:
            print('No smooth value given, using 5')
            n = 5
        for dat in left + right:
            temp_data = numpy.array(dat['data'][:, 1])
            for i in range(n, len(temp_data)-n):
                dat['data'][i, 1] = sum(temp_data[i-n:i+n]) / len(temp_data[i-n:i+n])
            print('smoothed')
        self.label_additions['ylabel_addition'] = \
            'Smoothed'
        return self.label_additions


class Massscale(object):
    def __init__(self, settings, plot_options, ggs=None):
        self.settings = settings
        self.label_additions = {
            'xlabel_addition': '',
            'y_left_label_addition': '',
            'y_right_label_addition': '',
        }

    def run(self, left, right):
        for dat in left + right:
            t = time.time()
            if dat['meta']['tof_p1_0'] is None:
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
            else:
                p1_0 = dat['meta']['tof_p1_0']
                p1_1 = dat['meta']['tof_p1_1']
                p1_2 = dat['meta']['tof_p1_2']
                dat['data'][:, 0] = p1_1*pow(dat['data'][:, 0] - p1_0, p1_2)
                print 'Found in db lookup!'
            print time.time() - t
        self.label_additions['xlabel_addition'] = \
            'Converted to mass'
        return self.label_additions


class Intensitymap(object):
    def __init__(self, settings, plot_options, ggs=None):
        self.settings = settings
        self.range = plot_options['xscale_bounding']
        if self.range is None:
            self.range = [0,0]
        self.label_additions = {
            'xlabel_addition': '',
            'y_left_label_addition': '',
            'y_right_label_addition': '',
        }

    def run(self, left, right):
        for dat in left + right:
            t = time.time()
            if dat['meta']['tof_p1_0'] is None:
                if dat['meta']['tof_R1_voltage'] == 0:
                    dat['data'][:, 1] = 0
                else:
                    tm.Voltages = {} # Contains all electrical values of the maching
                    tm.Voltages['pulse'] = dat['meta']['tof_pulse_voltage']
                    tm.Voltages['liner'] = dat['meta']['tof_liner_voltage']
                    tm.Voltages['R1'] = dat['meta']['tof_R1_voltage']
                    tm.Voltages['R2'] = dat['meta']['tof_R2_voltage']
                    coeff = tof_helpers.extrapolate()
                    dat['data'][:, 0] = ((dat['data'][:, 0]+0.1)/coeff[0])**(1/coeff[1])
            else:
                p1_0 = dat['meta']['tof_p1_0']
                p1_1 = dat['meta']['tof_p1_1']
                p1_2 = dat['meta']['tof_p1_2']
                dat['data'][:, 0] = p1_1*pow(dat['data'][:, 0]-p1_0, p1_2)
                print 'Found in db lookup!'
            print time.time() - t


            if self.range[1] > self.range[0]:
                start_index = numpy.argmax(dat['data'][:,0]>self.range[0])
                end_index =  numpy.argmax(dat['data'][:,0]>self.range[1])
            else:
                start_index = 100 # Cut off the very first part of the spectrum
                end_index = dat['data'].shape[0]
            old_size = end_index - start_index
            new_size = 650
            ratio = (old_size / new_size)+1
            print 'Ratio: ' + str(ratio)

            new = numpy.zeros((new_size, 2))
            for i in range(0, new_size):
                values = dat['data'][start_index+(ratio * i):start_index+(ratio * (i + 1)), 0]
                x_val = numpy.mean(values)
                values = dat['data'][start_index+(ratio * i):start_index+(ratio * (i + 1)), 1]
                try:
                    y_val = max(values)
                except ValueError:
                    y_val = 0
                #print('Mass: ' + str(x_val) + ', value: ' + str(y_val))
                new[i,0] = x_val
                new[i,1] = y_val
            dat['data'] = new

        self.label_additions['xlabel_addition'] = \
            'Converted to mass'
        return self.label_additions

class Intensitymap_wide(object):
    def __init__(self, settings, plot_options, ggs=None):
        self.settings = settings
        self.range = plot_options['xscale_bounding']
        if self.range is None:
            self.range = [0,0]
        self.label_additions = {
            'xlabel_addition': '',
            'y_left_label_addition': '',
            'y_right_label_addition': '',
        }

    def run(self, left, right):
        for dat in left + right:
            t = time.time()
            if dat['meta']['tof_p1_0'] is None:
                if dat['meta']['tof_R1_voltage'] == 0:
                    dat['data'][:, 1] = 0
                else:
                    tm.Voltages = {} # Contains all electrical values of the maching
                    tm.Voltages['pulse'] = dat['meta']['tof_pulse_voltage']
                    tm.Voltages['liner'] = dat['meta']['tof_liner_voltage']
                    tm.Voltages['R1'] = dat['meta']['tof_R1_voltage']
                    tm.Voltages['R2'] = dat['meta']['tof_R2_voltage']
                    coeff = tof_helpers.extrapolate()
                    dat['data'][:, 0] = ((dat['data'][:, 0]+0.1)/coeff[0])**(1/coeff[1])
            else:
                p1_0 = dat['meta']['tof_p1_0']
                p1_1 = dat['meta']['tof_p1_1']
                p1_2 = dat['meta']['tof_p1_2']
                dat['data'][:, 0] = p1_1*pow(dat['data'][:, 0]-p1_0, p1_2)
                print 'Found in db lookup!'
            print time.time() - t


            if self.range[1] > self.range[0]:
                start_index = numpy.argmax(dat['data'][:,0]>self.range[0])
                end_index =  numpy.argmax(dat['data'][:,0]>self.range[1])
            else:
                start_index = 100 # Cut off the very first part of the spectrum
                end_index = dat['data'].shape[0]
            old_size = end_index - start_index
            new_size = 650
            ratio = (old_size / new_size)+1
            print 'Ratio: ' + str(ratio)

            new = numpy.zeros((new_size, 2))
            for i in range(0, new_size):
                values = dat['data'][start_index+(ratio * i):start_index+(ratio * (i + 1)), 0]
                x_val = numpy.mean(values)
                values = dat['data'][start_index+(ratio * i):start_index+(ratio * (i + 1)), 1]
                try:
                    y_val = max(values)
                except ValueError:
                    y_val = 0
                #print('Mass: ' + str(x_val) + ', value: ' + str(y_val))
                new[i,0] = x_val
                new[i,1] = y_val
            dat['data'] = new

        self.label_additions['xlabel_addition'] = \
            'Converted to mass'
        return self.label_additions
