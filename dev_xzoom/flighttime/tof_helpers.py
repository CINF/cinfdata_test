import scipy as sp
import matplotlib as mpl
#mpl.use('Agg')
#import matplotlib.pyplot as plt
from scipy import optimize
import tof_model as tm



def extrapolate():
    """
    Uses the physical model on a few (currently 10) masses
    to create a fitting-expression for the flight-time as
    a function of mass.
    Currently the function takes no arguments, but obviously
    it will be nice to be able to set the range and number
    of masses used in the fit
    Args:
        None
    Returns:
        The two coefficients for the extrapolation
    Raises:
    """

    times = []
    masses = []
    for mass in range(1,50,5):
        ft = tm.flight_time(mass)
        times.append(ft[0]*1e6)
        masses.append(mass)

    times = sp.array(times)
    masses = sp.array(masses)

    # Fit the first set
    fitfunc = lambda p, x: p[0]*x**p[1] # Target function
    errfunc = lambda p, x, y: fitfunc(p, x) - y # Distance to the target function
    p0 = [1, 0.5] # Initial guess for the parameters
    p1, success = optimize.leastsq(errfunc, p0[:], args=(masses, times))

    time = sp.linspace(0, 100, 500)
    #plt.plot(masses, times, "ro", time, fitfunc(p1, time), "r-") # Plot of the data and the fit

    # Legend the plot
    #plt.title("Calculated Flighttime")
    #plt.xlabel("Mass [amu]")
    #plt.ylabel("Expected flighttime (microseconds)")
    #ax = axes()
    #text(0.8, 0.07,
    #     'x freq :  %.3f kHz' % (1/p1[1]),
    #     fontsize=16,
    #     horizontalalignment='center',
    #     verticalalignment='center',
    #     transform=ax.transAxes)

    #plt.show()

    print "Fitting function: time = {0} * mass^{1}".format(p1[0],p1[1])
    return p1

def draw_trajectory(mass):
    """
    Create a graph for expected flighttimes
    
    Args:
        mass: The mass to be calculated
    
    Returns:
    
    Raises:
    """
    res =  tm.flight_time(mass,0)
    res_slow =  tm.flight_time(mass,SLOW_POS)
    res_fast =  tm.flight_time(mass,FAST_POS)
    fig = plt.figure()
    ax11 = fig.add_subplot(111)
    #ax11.plot(res[3],res[2],res_slow[3],res_slow[2],res_fast[3],res_fast[2],'r-','b-','g-')
    ax11.plot(res[3],res[2],'r-')
    ax11.set_xlabel('Position / cm')
    ax11.set_ylabel('Time / micro seconds')
    plt.savefig('Trajectory.png')





def print_flighttimes(html, print_values,export_figure):
    """
    Print flight times of various masses. This is done by
    calling the extrapolate function and then use the
    returned expression to calculate the values
    Args:
        html: If true, the output will be formatted for a web-browser
        print_values...
        export_figure...
        
    Returns:
        The string formatted as desired
    
    Raises:
    """
    flight_times = []
    masses = []
    coeff = extrapolate()
    returnString = ""
    for mass in range(1,184): 
        res = coeff[0] * (mass ** coeff[1])
        if print_values:
            #print "Flight time of {}: {:.3f} microsceonds".format(mass,res[0]*1e6)
            returnString = returnString +  "{0} {1:.3f}".format(mass,res)
            if html:
                returnString = returnString + "<br>"
        returnString = returnString + "\n"
        masses.append(mass)
        flight_times.append(res)
    if export_figure:
        fig = plt.figure()
        ax11 = fig.add_subplot(111)
        ax11.plot(masses,flight_times,'r-')
        ax11.set_xlabel('Mass / AMU')
        ax11.set_ylabel('Flight Time / micro seconds')
        plt.savefig('Masses.png')
    return returnString



