#!/bin/env python
# -*- coding: utf-8 -*-

import struct
from collections import namedtuple
import numpy as np
from optparse import OptionParser

# set HOME environment variable to a directory the httpd server can write to
import os, sys
os.environ[ 'HOME' ] = '/var/www/cinfdata/figures'
# System-wide ctypes cannot be run by apache... strange...
sys.path.insert(1, '/var/www/cinfdata')

import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt

from pylab import *
from scipy import mgrid

import json

""" ======== MUL file specification ========

Gwyddion is used as the free reference implementation for this data
format. Fro details on the struct for the data, see:
http://sourceforge.net/p/gwyddion/code/HEAD/tree/trunk/gwyddion/modules/file/mulfile.c

The mul file starts with an index of 64 pairs (one per image),
which consist of:

image_nr (h)
position (l)

An image number of 0 means end of data. The position is measured in
blocks of 128 bytes.

The (h) and (l) refer to the data type designations in the struct
module (see http://docs.python.org/2/library/struct.html for
details). The ones used in this data format are:
h: short signed int, 2 bytes
l: long signed int, 4 bytes
p: Pascal type string

Afte the index follows the images. Each image consist of a label,
spare data and the image data. The label consist of:

id (h)
size (h)(in blocks)
xres, yres, zres (h)(x and y res are in pixels)
year, month, day, hour, minute, second (h)
xdim, ydim (h)(In Angström)
xoff, yoff (h)(In In Angström)
zscale (h)(In Volts)
tilt (h)
speed (h, actually duration, res=raw/100[s])
bias (h, res=-10.0*raw/32768.0 [V])
current (h, res=raw/100 [nA])
sample (p, 20 byte chars + 1, charset=windows-1252)
title (p, 20 byte chars + 1, charset=windows-1252)
postpr, postd1 (h)
mode (h, 0 is height, 1 is current, for details on rest see Gwyddion)
curr_factor (h)
n_point_scans (h)
unitnr (h)
version (h)
16 spares (h, numbered 48 through 63)
"""

        


class ParseMulFile():
    """ General comment """

    def __init__(self, filepath):
        """ Init method """
        self.filepath = filepath
        ### Open file in binary mode for reading
        self.file = open(self.filepath, 'rb')
        ### Initialize data structures
        self.data = []
        self.tmp_single_data = None
        self.fig = None
        
        ### Create the structs for reading from the mul-files
        # Index struct
        self.index_struct = struct.Struct('<hl')
        # Label struct, names
        self.label_names = ['nr', 'size', 'xch', 'ych', 'zch', 'year', 'month',
                            'day', 'hour', 'minute', 'second', 'xsize', 'ysize',
                            'xshift', 'yshift', 'zscale', 'tilt', 'speed',
                            'bias', 'current', 'sample', 'title', 'postpr',
                            'postd1', 'constheight', 'Currfac', 'R_Nr',
                            'unitnr', 'version']
        # Structs (separate one for the spare, because we want to read them in
        # as a list)
        self.label_names_struct = struct.Struct('<5h6h6hhhh21p21p7h')
        self.label_spare_struct = struct.Struct('<16h')

    def read_index(self):
        """ Read index """
        # Go to the beginning of the file
        self.file.seek(0)

        # For each of the 64 entries in the index
        for i in range(64):
            # Read the current index from file and unpack
            index = self.index_struct.unpack_from(
                self.file.read(self.index_struct.size)
                )
            # If the index describes an image, save it
            if index[0] != 0:
                self.data.append({'index':index,'label':None})

    def read_all_metadata_raw(self):
        for image in self.data:
            self.file.seek(image['index'][1] * 128)
            # Read all of the label except the 'spare' variable
            label_items = self.label_names_struct.unpack_from(
                self.file.read(self.label_names_struct.size)
                )
            label = dict(
                [[name, datum] for name, datum in zip(self.label_names, label_items)]
                )

            # Read the spare variable
            label['spare'] = self.label_spare_struct.unpack_from(
                self.file.read(self.label_spare_struct.size)
                )

            dir_, file_ = os.path.split(self.filepath)
            month = os.path.split(dir_)[1]

            # Add the filename to the metadata
            label['thumbnail_path'] = self.figure_path(month, file_, image['index'][0], relative=True)

            ### Do some processing on the raw values
            # Decode special characters in the strings with the windows-1252
            # and replace with � in case of errors
            label['title'] = label['title'].decode('windows-1252', 'replace')
            label['sample'] = label['sample'].decode('windows-1252', 'replace')
            # Calculate real bias and current
            label['speed'] = label['speed'] / 100.0  # durations, seconds
            label['bias'] = -10.0 * label['bias'] / 32768.0  # Volts
            label['bias_corrected'] = label['bias'] - 1e-6 * label['current']
            label['current'] = label['current'] / 100.0  # nA

            image['label'] = label

        return self.data

    def read_all_metadata(self):
        return json.dumps(self.read_all_metadata_raw())

    def write_images_if_necessary(self):
        dir_, file_ = os.path.split(self.filepath)
        month = os.path.split(dir_)[1]
        first_image_path = self.figure_path(month, file_, 1)
        if os.path.exists(first_image_path):
            return
        self.tmp_single_data = None
        for image in self.data:
            filename = self.figure_path(month, file_, image['index'][0])
            # The last 128 is the one block that is added from the metadata
            self.file.seek(image['index'][1] * 128 + 128)
            ### Read the image data
            # Calculate the image size
            image_size = image['label']['xch'] * image['label']['ych']
            # Read the raw data
            image_struct = struct.Struct('<' + str(image_size) + 'h')
            # Unpack it and store into an array
            data = np.array(image_struct.unpack_from(self.file.read(image_struct.size)))
            # Reshape it to be 'xch' by 'ych'
            data.resize(image['label']['ych'], image['label']['xch'])
            # Append the data
            image['data'] = data
            self.make_single_thumbnail(image, filename)

    def figure_path(self, month, file_, index, relative=False):
        if relative:
            filename = "../figures/{0}_{1}_{2}.png".\
                format(month, file_, index)
        else:
            filename = "/var/www/cinfdata/figures/{0}_{1}_{2}.png".\
                format(month, file_, index)
            
        return filename

    def make_single_thumbnail(self, image, path, size=256):
        """ Make a single thumbnail """
        xch = image['label']['xch']
        ych = image['label']['ych']
        pix_index = arange(xch)
        x_korr = ones(xch) * image['label']['spare'][7] * 0.01
        y_korr = ones(ych) * image['label']['spare'][8] * 0.01
        m = array([pix_index, y_korr])
        n = array([x_korr, pix_index]).transpose()

        correction = np.dot(n, m)

        # Subtract the correction and invert
        data = (image['data'] - correction) * -1
    
        self.imsave(path, data, size, cmap=cm.hot)

    def imsave(self, filename, X, image_size, **kwargs):
        """ Homebrewed imsave to have nice colors... """
        figsize=[image_size/100.0]*2
        rcParams.update({'figure.figsize':figsize})
        self.fig = figure(figsize=figsize)
        axes([0,0,1,1]) # Make the plot occupy the whole canvas
        axis('off')
        imshow(X,origin='lower', **kwargs)
        savefig(filename, facecolor='black', edgecolor='black',
                dpi=100)
        close(self.fig)

    def close_file(self):
        self.file.close()

                

if __name__ == '__main__':
    PARSER = OptionParser()
    PARSER.add_option('--file')
    (options, args) = PARSER.parse_args()
    p = ParseMulFile(options.file)

    p.read_index()
    metadata = p.read_all_metadata()
    p.write_images_if_necessary()
    p.close_file()
    print metadata


class Old:
    """ This class is kept only because the method below has information about the meta data fields """

    def print_metadata(self, image):
        # Print out the metadata nicely
        print '##############################################################################'
        print 'Image nr:', image['label']['nr'], 'size:', image['label']['size'], 'bytes'
        print 'Resolution:', image['label']['xch'], 'by', image['label']['ych'], 'pixel (xch, ych)'
        #print 'zch', image['label']['zch']
        print 'Recorded: {0}-{1}-{2} {3}:{4}:{5}'.format(
            image['label']['year'], image['label']['month'], image['label']['day'],
            image['label']['hour'], image['label']['minute'], image['label']['second'])    
        print 'Size:', image['label']['xsize'], 'by', image['label']['ysize'], 'Angstrom (xsize, ysize)'
        print 'Position:', str(image['label']['xshift']) + ',', image['label']['yshift'], 'Angstrom (xshift, yshift)'
        print 'zscale:', image['label']['zscale'], 'V, tilt:', image['label']['tilt'],\
            'Scan duration:', float(image['label']['speed'])/100, 's'
        print 'Bias:', round(float(image['label']['bias'])/-3276.3, 3),\
            'V, Current:', round(float(image['label']['current'])/100, 3), 'nA'
        print 'Sample:', image['label']['sample']
        print 'Title:', image['label']['title']
        print 'postpr', image['label']['postpr'],\
            'postd1', image['label']['postd1'],\
            'constheight', image['label']['constheight'],\
            'Currfac', image['label']['Currfac'],\
            'R_Nr', image['label']['R_Nr'],\
            'unitnr', image['label']['unitnr'],\
            'version', image['label']['version']
        print 'spare:', image['label']['spare']
