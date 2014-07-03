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

### color
import random
import matplotlib

class Color:
    """The Color class is a small utility class with two purposes.
    1) To provide colors for the graph lines. This is done by first taking
       colors from a list of good colors, then second, when we run out of those
       to give random color.
    2) Second, it is the wrapper for a bit of figure code that colors the axis.
       This includes the graph line, the graph ticks and graph labels.
    """
    def __init__(self):
        """The init method. Initilalises the color list and the plot counter."""
        self.n_plots = -1
        self.colors = ['b', 'r', 'k', 'm', 'g', 'c', 'y']
        self.c = matplotlib.colors.ColorConverter()

    def get_color(self):
        """Return the next color for the graph line."""
        self.n_plots += 1
        if self.n_plots < 7:
            return self.colors[self.n_plots]
        else:
            # If we run out of predefined color use a randomly generated one
            return (random.random(), random.random(), random.random())

    def get_color_hex(self):
        """ Get color in hes """
        color = self.get_color()
        return matplotlib.colors.rgb2hex(self.c.to_rgb(color))
        
    def color_axis(self, left_axis, right_axis, left_color, right_color):
        """This method is used to color the axis of a plot, including the axis
        line, the ticks and the labels. It calls two other internal methods:
          _color_spine(self, axis, left_color, right_color)
          _color_ticks_and_labels(self, obj, color)        
        that does all the work
        """
        if right_axis:
            self._color_spine(left_axis, left_color, right_color)
            self._color_ticks_and_labels(left_axis.get_yaxis(), left_color)
            self._color_ticks_and_labels(right_axis.get_yaxis(), right_color)
        else:
            self._color_spine(left_axis, left_color, 'black')
            self._color_ticks_and_labels(left_axis.get_yaxis(), left_color)

    def _color_spine(self, axis, left_color, right_color):
        """This method is used to color the line that forms the axis, which is
        one of the spines of the plot
        """
        # Get the children of the axis
        for n, child in enumerate(axis.get_children()):
            # If the child is an instance of spince, the class for the frame
            if isinstance(child, matplotlib.spines.Spine):
                # There are 8 spines, number 8 is the left frame, number 6 the
                # right frame
                if n == 8:
                    child.set_color(left_color)
                elif n == 6:
                    child.set_color(right_color)
    
    def _color_ticks_and_labels(self, obj, color):
        """This method is used to color the ticks and the labels of an axis.
        The method recursively checks if the object it recieves, is one that
        requires coloring and then calls itself on all the children of the
        object.
        """
        # The string representation of the ticks are "Line2D()" and for the
        # labels they are "Text(0,0,\'\')"
        if str(obj) == 'Line2D()' or str(obj) == 'Text(0,0,\'\')':
            obj._color = color

        # Call itself on the children of obj
        children = obj.get_children()
        for child in children:
            self._color_ticks_and_labels(child, color)
