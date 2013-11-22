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

    def get_color(self):
        """Return the next color for the graph line."""
        self.n_plots += 1
        if self.n_plots < 7:
            return self.colors[self.n_plots]
        else:
            # If we run out of predefined color use a randomly generated one
            return (random.random(), random.random(), random.random())
        
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
        

### TimeMarks
from datetime import datetime
import time

class TimeMarks:
    """The TimeMarks class i designed give a set of nicely spaced and
    positioned time mark strings, between two datetimes provided in Unix time.

    NOTE: The class has no way to nicely align timemarks over months or years.
    Therefore, it is best suited for time differences which is no more than a
    few months.
    """
    def __init__(self, start, end, marks_max=13, interval=None, 
                 markformat='%b-%d %H:%M'):
        """The __init__ method initializes the different variables necessary
        to calculate the time marks. It can also be used to reinitilize the
        object

        Keywork arguments:
        start       -- Start point in Unix time
        end         -- End point in Unix time
        marks_max   -- Maximum number of marks returned (default 13)
        interval    -- Specify interval between marks. If none is provided
                       (recommended) the class will pick the most appropriate
                       'nice' interval (The predefined 'nice' intervals is
                       accessible as the 'intervals_s' variable).
        markformat  -- Specify the strftime format for the mark strings.
                       (default is '%b-%d %H:%M' e.g. Jan-17 14:48).

        The __init__ method calls the _find_interval(self) method at the end
        to indentify the correct interval.
        """
        if start > end:
            start, end = end, start
        # _s appended variables are in seconds. If they are datetime the
        # seconds are UNIX time
        self.start_s = start
        self.end_s = end
        self.delta_s = self.end_s - self.start_s
        self.interval_s = interval
        self.marks_max = marks_max
        # The variable for the first nicely places mark
        self.mark_start = None
        self.markformat = markformat
        self._find_interval()

    def _find_interval(self):
        """If the interval between time marks is not explicitly given, this
        find the most appropriate "nice" interval
        """
        if not self.interval_s:
            m = 60
            h = 60*60
            d = 60*60*24
            # Nice intervals
            self.intervals_s = (1, 5, 10, 15, 30,
                                m, 2*m, 3*m, 5*m, 6*m, 10*m, 15*m, 20*m, 30*m,
                                h, 2*h, 3*h, 4*h, 6*h, 8*h, 12*h,
                                d, 2*d, 3*d, 4*d, 7*d, 14*d, 21*d, 28*d)

            # Find the right nice interval, that gives the right number of time
            # of time marks
            self.interval_s = 0
            for interval in self.intervals_s:
                if float(self.delta_s) / interval < self.marks_max:
                    self.interval_s = interval
                    break
            # If no interval is found, the range is larger then marks_max*28*d
            # Then we resort to non-nice intervals
            if self.interval_s == 0:
                self.interval_s = int(self.delta_s/self.marks_max)
                
    def get_time_marks(self):
        ''' This method returns a list of tuples, each tuple contain the Unix
        time and the strf time formatted time mark strings. The method does
        three things:
        1) Identify a nice starting time, that coincides with an intiger of
        the interval.
        2) Find all nice times marks and return them.
        '''
        # Convert the interval to a (days, hour, minutes, seconds) tuple
        # This sucks, there should be a builtin somewhere for this
        intervaltuple = (self.interval_s // (24*60*60),\
                         self.interval_s % (24*60*60) // (60*60),\
                         self.interval_s % (60*60) // 60,\
                         self.interval_s % 60)

        # If there is more than 1 value in the interval that is not 0
        if intervaltuple.count(0) < len(intervaltuple)-1:
            mark = self.start_s
        else:
            # Find the first mark
            mark = self._find_start(self.start_s, self.end_s,\
                                           intervaltuple)

        # Create the marks
        marks_x = []
        marks_y = []
        while mark <= self.end_s:
            # Append (mark, timestring) tuple to marks
            marks_x.append(mark)
            marks_y.append(time.strftime(\
                    self.markformat, datetime.fromtimestamp(mark).timetuple()))
            mark += self.interval_s

        return tuple(marks_x), tuple(marks_y)
    
    def _find_start(self, start, end, intervaltuple):
        ''' Finds the "nice" start value. A nice value is one where e.g. if the
        interval is 2 hours, then the hours value has to be dividable with 2
        with no remainder, and all smaller values should be 0. Goes through all
        the value from start to end untill it finds a nice start value.
        '''
        
        # INEFFICIENT, NEEDS REWRITE
        
        start_mark = start
        # Go through all possible values
        n=start-1
        while n <= end:
            n+=1
            # Convert the timestamp to a timetuple:
            # (0   , 1    , 2  , 3   , 4     , 5     , 6      , 7      , 8    )
            # (year, month, day, hour, minute, second, weekday, yearday, isdst)
            t = datetime.fromtimestamp(n).timetuple()
            # Day interval, intervaltuple[0]
            if intervaltuple[0] > 0:
                if t[2] % intervaltuple[0] == 0 and sum(t[3:6]) == 0:
                    start_mark = n
                    break
            # Hour interval, intervaltuple[1]
            elif intervaltuple[1] > 0:
                if t[3] % intervaltuple[1] == 0 and sum(t[4:6]) == 0:
                    start_mark = n
                    break
            # Minute interval, intervaltuple[2]
            elif intervaltuple[2] > 0:
                if t[4] % intervaltuple[2] == 0 and sum(t[5:6]) == 0:
                    start_mark = n
                    break
            # Seconds interval, intervaltuple[3]
            elif intervaltuple[3] > 0:
                if t[5] % intervaltuple[3] == 0:
                    start_mark = n
                    break
                    
        return start_mark
    
if __name__ == "__main__":
    # Testcases. This will only be run if you execute this file. Just ignore if
    # you are only interested in figuring out how the class works
    t1 = time.time()
    now = int(time.time())

    print 'Range: 10 m'
    t = TimeMarks(now - 600, now)
    marks = t.get_time_marks()
    print 'Interval:', t.interval, 's'
    print 'Number of marks:', len(marks)
    print marks
    print

    print '3600 s'
    t = TimeMarks(now - 3600, now)
    marks = t.get_time_marks()
    print 'Interval:', t.interval, 's'
    print 'Number of marks:', len(marks)
    print marks
    print

    print '12 h'
    t = TimeMarks(now - 12*3600, now)
    marks = t.get_time_marks()
    print 'Interval:', t.interval, 's'
    print 'Number of marks:', len(marks)
    print marks
    print

    print '24 h'
    t = TimeMarks(now - 24*3600, now)
    marks = t.get_time_marks()
    print 'Interval:', t.interval, 's'
    print 'Number of marks:', len(marks)
    print marks
    print

    print '### Special cases ###'
    print '1 s'
    t = TimeMarks(now - 1, now)
    marks = t.get_time_marks()
    print 'Interval:', t.interval, 's'
    print 'Number of marks:', len(marks)
    print marks
    print

    print 'since epoch'
    t = TimeMarks(0, now)
    marks = t.get_time_marks()
    print 'Interval:', t.interval, 's'
    print 'Number of marks:', len(marks)
    print marks
    print
