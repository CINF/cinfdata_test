import MySQLdb
cnxn = MySQLdb.connect(host="127.0.0.1", user="CINFLAB",passwd = "CINF123", db = "new_db")
import numpy as np
import matplotlib.pyplot as plt
import sys

# Command to start script. In case the script is started without information on
# which dataset to fit, it will ask for it
if len(sys.argv) >= 2:
    dataset = int(sys.argv[1])
else:
    dataset = input('Insert spectrum number ')

# Command to connect to Robert's mySQL database through putty and get
# "dataset", which is the data to be analyse.
cursor = cnxn.cursor()
cursor.execute("select x,y FROM xy_values_bifrost where measurement = %i order by id;"  % dataset)
data = np.array(cursor.fetchall())

# Getting time of the mass-scan from Robert's mySQL database
cursor.execute("""SELECT unix_timestamp(time) FROM new_db.measurements_bifrost where
               id=%i;""" %dataset)
time = cursor.fetchall()
time = time[0][0]

# Getting the nearest pressure measurement and the corresponding time after the
# mass-scan from mySQL database
cursor.execute("""SELECT pressure, unix_timestamp(time) from pressure_sputterkammer where
                unix_timestamp(time) > %i order by time asc limit 1;"""  %time)
pressure11 = cursor.fetchall()
pressure1 = pressure11[0][0]
time1 = pressure11[0][1]/1.0

# Getting the nearest pressure measurement and the corresponding time before the
# mass-scan from Robert's mySQL database
cursor.execute("""SELECT pressure, unix_timestamp(time) from pressure_sputterkammer where
                unix_timestamp(time) < %i order by time desc limit 1;""" %time)
pressure22 = cursor.fetchall()
pressure2= pressure22[0][0]
time2 = pressure22[0][1]/1.0

# Calculating a weighted average for the two pressure measurements
pressuretotal = ((time1-time)/(time1-time2)*pressure1+
                 (time-time2)/(time1-time2)*pressure2)

# Datasets of compounds, and their peak position and intensity based on
# cracking patterns. The variable name is the name of the compound. The first
# number is giving the peak position and the second the intensity

H2_spec = {1.2:0.03, 2.2:1} # Table
He_spec = {2.2:0.32, 4.1:1}
CH4_spec = {1:0.165, 12:0.03, 13:0.078, 14:0.16, 15:0.85, 16:1, 17:0.012} # Table
F_spec = {19:1} # Table
H2O_spec = {1:0.024, 16:0.018, 17:0.26, 18:1} # Table
Ne_spec = {20:1, 22:0.102} # Table
N2_spec = {7:0.0009, 14:0.14, 15:0.00022, 28:1, 29:0.0069} 
CO28_spec ={6:0.001, 8:0.0002, 12:0.060, 13:0.00058, 14:0.012, 16:0.024,
            28:1, 29:0.011, 30:0.0018}
CO29_spec = {6.5:0.0007, 8:0.0001, 12:0.00046, 13:0.043, 14.5:0.0097,
             16:0.020, 18:0.00052, 28:0.0065, 29:1, 30:0.0076, 31:0.031,}
NO_spec = {7:0.0005, 8:0.0002, 14:0.081, 15:0.054, 16:0.015,
           30:1, 31:0.0031, 32:0.0015}
O2_spec = {16:0.18, 32:1, 34:0.004} # Table
Ar_spec = {20:0.226, 36:0.0034, 38:0.0006, 40:1}
CO2_spec = {12:0.097, 16:0.16, 22:0.021, 28:0.13, 44:1, 45:0.012} # Table
refs = {'H2' : H2_spec,
        'He' : He_spec,
        'CH4' : CH4_spec,
	'F'   : F_spec,
        'H2O' : H2O_spec,
        'Ne' : Ne_spec,
        'N2' : N2_spec,
        'CO28' : CO28_spec,
        'CO29' : CO29_spec,
        'NO' : NO_spec,
        'O2' : O2_spec,
        'Ar' : Ar_spec,
        'CO2' : CO2_spec}

# The dictionary ionprob is containing the relative probabilities of
# ionization refered to nitrogen approx. 100 eV electron energy.
ionprob = {'H2' : 0.44,
           'He' : 0.15,
           'CH4' : 1.6,
	   'F' : 1,
           'H2O' : 1.0,
           'Ne' : 0.30,
           'N2' : 1.0,
           'CO28' : 1.05,
           'CO29' : 1.05,
           'NO' : 1.2,
           'O2' : 1.0,
           'Ar' : 1.2,
           'CO2' : 1.4}

# Defining the steps between masses
steps = data[1,0]-data[0,0]

# "Start" is the start point of the function "makepeak"
start = data[0,0]

# Getting the size of the data so the dimensions of the weight funktion theta
# will match the data. It is used in makepeak so the peak will be in the
# interval of start to start + ndata.
ndata = len(data)


def makespectra(steps,ndata,start,refdata,ionprob,width=0.2):
    """Make reference spectra from reference data, by scaling
    the gaussian kurves from the funktion makepeak. The
    reference spectra for each of the compounds are found in
    refs. These reference spectra are used to fit them to the
    measured data.

    steps is the mass step between data points 

    ndata is the number of data points in the measured spectrum

    start is setting the start point of makepeak

    refdata is a dictionary with the peak positions and relative heights.
    These are used to make a gaussian peak, to represent the signal of a
    given molecule.
    
    ionprop is a dictionary containing value of the ionization probability
    of the compounds

    width is the half-width of the peak. Peaks are gaussian-shaped.

    The function returns the referencespectra and a list of compounds
    used to make the reference spectra.
    """
    
    referencespectra = np.zeros((ndata,len(refdata)))

    compounds = []
    i = 0
    
    # The first loop are running through all the compounds
    for comp,spektrum in refdata.items():
        compounds.append(comp)
        
        # This loop are extracting positions and intensities, and creates a
        # gaussian peak at each position with the makepeak function.
        # The gaussian peaks are then scaled with the intensities and
        # ionization probabilities.
        for position, intensity in spektrum.items():
            referencespectra[:,i] += (intensity / ionprob[comp]
                                    * makepeak(position, steps, ndata, start))
        i += 1

    return (compounds,referencespectra)

def makepeak(position,steps,ndata,start,width=0.2):
    """Makes the gaussian curves used to fit

    position is giving the center of the gaussian curve

    steps is the mass step between points

    ndata is the number of data points in the measured spectrum

    start is setting the start point of the function

    width is the width of the gaussian curve

    the function returns a gaussian curve to fit compound abundance
    """

    #generating the x-values for the gaussian kurve os they match the
    #x-value for the dataset
    x = np.arange(start, start+steps*ndata - 0.5*steps, steps)
    return 1/(2*np.pi*width*width)* np.exp(-(x-position)**2/(2*width*width))

def makeanalyse (steps,ndata,start,refdata,ionprob,width=0.2):
    """Makes the normalized weight function, numtheta, which creates the best
    fit y to the scan and makes a list of compounds.

    steps is the mass step between data points 

    ndata is the number of data points in the measured spectrum

    start is setting the start point of makepeak

    refdata is a dictionary with the peak positions and relative heights. These
    are used to make a gaussian peak, to represent the signal of a given
    molecule.
    
    ionprop is a dictornary containing value of the ionisation properbility
    of the compoundens

    width is the half-width of the peak. Peaks are gaussian-shaped.

    this function is calling the function makespectra for making a
    referance spectra. Then it adds an offset for fitting background noise,
    generating the best fit by calculating the weight function, theta. 
    """

    # Calling the function makespectra for generating the referencespectra
    compounds, refspectra = makespectra(steps,ndata,start,refdata,ionprob,width=0.2)

    # Offset is added to the reference spectra to
    # fit the backgroundnoise
    offset = np.ones(ndata)
    offset.shape
    ref2 = np.zeros((refspectra.shape[0], refspectra.shape[1]+1))
    ref2[:,:-1] = refspectra
    ref2[:,-1] = offset
    refspectra = ref2


    # Generating the weight function theta
    pseudo = np.linalg.pinv(refspectra)
    theta = np.dot(pseudo,np.array(data[:,1]))
    
    # The y-values of the best fit is calculated
    y = np.dot(refspectra,theta)

    # The weight function theta is normalized and saved in the variable
    # numtheta
    theta2 = theta[0:len(theta)-1]
    num = theta2.sum()
    numtheta = theta2 / num

    return (numtheta,y,compounds)


# Generating the weight function numtheta, the best fit y and a list of
# compounds
numtheta, y, compounds = makeanalyse (steps,ndata,start,refs,ionprob)

print "deleted molecules"

# This loop is used for removing negative values in numtheta, if negative
# values occur in numtheta.
while numtheta[numtheta.argmin()] <0:

    # Identification of the negative value in numtheta (baddata)
    baddata = numtheta.argmin()
    c = compounds[baddata]

    # Printing the name of removed compounds and printing them so
    # the first removed compound will be at the top of the list.
    print "Deleting: ", c

    del refs[c]

    # Generating the weight function numtheta, the best fit y and a list of
    # compounds
    numtheta, y, compounds = makeanalyse (steps,ndata,start,refs,ionprob)

# Calculating the absolute pressure for a given molecule from pressuretotal
# and numtheta
pressuremolekyle = pressuretotal * np.array(numtheta)

# Print command for readable output
print '-----------'

# Root mean square error is calculated
error = np.array(data[:,1]) - y
RMS = np.sqrt((error * error).sum())/len(error)
print 'Error is'
print RMS

# Print command for readable output
print '-----------'
print "Compound   Relative abundance"

# Arranging the data in a readable form and printing. At the left the
# compounds are listed, and at the right their percentage representation in
# the sample.

order = np.argsort(numtheta)[::-1]
for i in range(len(compounds)):
    j = order[i]
    print "%4s %17.3f" % ( compounds[j], numtheta[j])
print "Sum:", numtheta.sum()

# Print command for readable output
print '-----------'
print "Compound   Pressure (mbar)"


# Arranging the data in a readable form and printing. To the left the
# compounds are listed, and to the right their absolute concetration in the
# sample.

order = np.argsort(pressuremolekyle)[::-1]
for i in range(len(compounds)):
    j = order[i]
    print "%4s %15.3g" % ( compounds[j], pressuremolekyle[j])
print "Sum:", pressuremolekyle.sum()

#Plotting the data as red, and the best fit as blue
#plt.plot(data[:,0],y, 'b-')
#plt.plot(data[:,0],data[:,1], 'r-')
#plt.xlabel('mass (amu)')
#plt.ylabel('SEM current (A)')
#plt.title('graph (red) and fit (blue) of NO' )
#plt.show()

