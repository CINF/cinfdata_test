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
