#!/bin/env python
# -*- coding: utf-8 -*-
# pylint: disable=E1101
# E1101 are false positives

""" This file is used to parse Århus STM type file (MUL-files) and
return metadata and generate thumbnails for the images.

======== MUL file specification ========

Gwyddion is used as the free reference implementation for this data
format. Fro details on the struct for the data, see:
http://sourceforge.net/p/gwyddion/code/HEAD/tree/trunk/gwyddion/modules/file
/mulfile.c

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
16 spares (h, numbered 48 through 63, the tilt correcttions applied during
scan are number 55 and 56)
"""

import struct
import numpy as np
from optparse import OptionParser

# set HOME environment variable to a directory the httpd server can write to
import os
if os.getuid() == 48:  # 48 is Apache
    import sys
    os.environ['HOME'] = '/var/www/cinfdata/figures'
    # System-wide ctypes cannot be run by apache... strange...
    sys.path.insert(1, '/var/www/cinfdata')

import matplotlib
matplotlib.use('Agg')
import pylab
import json


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

        ### Create the structs for reading from the mul-files
        # Label names
        self.label_names = ['nr', 'size', 'xch', 'ych', 'zch', 'year', 'month',
                            'day', 'hour', 'minute', 'second', 'xsize',
                            'ysize', 'xshift', 'yshift', 'zscale', 'tilt',
                            'speed', 'bias', 'current', 'sample', 'title',
                            'postpr', 'postd1', 'constheight', 'Currfac',
                            'R_Nr', 'unitnr', 'version']
        # Structs: For the label items before the spare, there are 20
        # h variables before the two strings and an additional 7
        # afterwards. (see specification above). The spares have a
        # struct of their own because we want to put them in their own
        # data structure
        self.structs = {'index': struct.Struct('<hl'),
                        'label_names': struct.Struct('<20h21p21p7h'),
                        'label_spare': struct.Struct('<16h')}

    def read_index(self):
        """ Read index """
        # Go to the beginning of the file
        self.file.seek(0)

        # For each of the 64 entries in the index
        for _ in range(64):
            # Read the current index from file and unpack
            index = self.structs['index'].unpack_from(
                self.file.read(self.structs['index'].size)
                )
            # If the index describes an image, save it
            if index[0] != 0:
                self.data.append({'index': index, 'label': None})

    def read_all_metadata_raw(self):
        """ Read all the metadata from a MUL-file. The method will
        also do minimal data manipulation like decoding from the
        character set of the strings and converting integer values
        into their proper float value (see MUL-file specification
        above)

        The metadata is added to the image dict under the 'label' key
        """
        for image in self.data:
            self.file.seek(image['index'][1] * 128)
            # Read all of the label except the 'spare' variable
            label_items = self.structs['label_names'].unpack_from(
                self.file.read(self.structs['label_names'].size)
                )
            label = {}
            for name, data in zip(self.label_names, label_items):
                label[name] = data

            # Read the spare variable and put them in a dict, with the
            # proper index between 48 and 63 (see specification)
            spare = self.structs['label_spare'].unpack_from(
                self.file.read(self.structs['label_spare'].size)
                )
            label['spare'] = dict(zip(range(48, 64), spare))

            dir_, file_ = os.path.split(self.filepath)
            month = os.path.split(dir_)[1]

            # Add the filename to the metadata
            label['thumbnail_path'] = self.figure_path(
                month, file_, image['index'][0], relative=True)

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
        """ Read all the metadata and return it as json """
        return json.dumps(self.read_all_metadata_raw())

    def images_missing(self):
        """ Determine if there needs to be made thumbnails """
        dir_, file_ = os.path.split(self.filepath)
        month = os.path.split(dir_)[1]
        first_image_path = self.figure_path(month, file_, 1)
        return not os.path.exists(first_image_path)

    def write_images(self):
        """ Read the data for the images and save thumbnails """
        dir_, file_ = os.path.split(self.filepath)
        month = os.path.split(dir_)[1]
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
            data = np.array(
                image_struct.unpack_from(self.file.read(image_struct.size)))
            # Reshape it to be 'xch' by 'ych'
            data.resize(image['label']['ych'], image['label']['xch'])
            # Append the data
            image['data'] = data
            self.make_single_thumbnail(image, filename)

    @staticmethod
    def figure_path(month, file_, index, relative=False):
        """ Produce either the absolute or the relative form of the
        figure path from the month, the filename and the index.
        """
        filename = "../figures/{0}_{1}_{2}.png".format(month, file_, index)
        if not relative:
            filename = os.path.abspath(filename)
        return filename

    def make_single_thumbnail(self, image, path):
        """ Make a single thumbnail """
        xch = image['label']['xch']
        ych = image['label']['ych']
        pix_index = pylab.arange(xch)
        x_korr = pylab.ones(xch) * image['label']['spare'][55] * 0.01
        y_korr = pylab.ones(ych) * image['label']['spare'][56] * 0.01
        m = pylab.array([pix_index, y_korr])
        n = pylab.array([x_korr, pix_index]).transpose()

        correction = np.dot(n, m)

        # Subtract the correction and invert
        data = (image['data'] - correction) * -1

        self.imsave(path, data, (xch, ych), cmap=pylab.cm.hot)

    @staticmethod
    def imsave(filename, data, image_size, **kwargs):
        """ Homebrewed imsave to have nice colors... """
        figsize = [image_size[0] / 100.0, image_size[0] / 100.0]
        pylab.rcParams.update({'figure.figsize': figsize})
        fig = pylab.figure(figsize=figsize)
        pylab.axes([0, 0, 1, 1])  # Make the plot occupy the whole canvas
        pylab.axis('off')
        pylab.imshow(data, origin='lower', **kwargs)
        pylab.savefig(filename, facecolor='black', edgecolor='black',
                dpi=100)
        pylab.close(fig)

    def close_file(self):
        """ Close the mul file """
        self.file.close()


def main():
    """ Main method when the parser is used as a script """
    parser = OptionParser()
    parser.add_option('--file')
    parser.add_option('--thumbs', action='store_true', default=False)
    options, _ = parser.parse_args()
    mul_file = ParseMulFile(options.file)

    if options.thumbs:
        if mul_file.images_missing():
            mul_file.read_index()
            metadata = mul_file.read_all_metadata()
            mul_file.write_images()
            mul_file.close_file()
    else:
        mul_file.read_index()
        metadata = mul_file.read_all_metadata()
        if mul_file.images_missing():
            mul_file.write_images()
            mul_file.close_file()
        print metadata

if __name__ == '__main__':
    main()
