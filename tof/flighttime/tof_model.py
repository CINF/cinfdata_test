global Voltages

#These should properly be stored in a bit more systematic way...
global FAST_POS
global SLOW_POS
SLOW_POS =  0.62
FAST_POS = -0.62

def field(pos):
    """
    Calculates the electric field at a given position in the instrument.
    All distances are measured from CAD drawing.
    The result is given in V/cm.
    
    Args:
        pos: the position in cm in the instrument. pos=0 is defined to be at the center axis of the incoming ions
    
    Returns:
        Tuple containing field and distance until it needs to be checked again in either direction
        
    Raises:
        ....
    """
        
    Params = []
    Params.append((-0.635,Voltages['pulse']))   #A1
    Params.append((0.635,0))                    #A2
    Params.append((1.9685,Voltages['liner']))   #A3
    Params.append((95.235,Voltages['liner']))   #Liner grid
    Params.append((96.390,Voltages['R1']))      #R1
    Params.append((110.81,Voltages['R2']))      #R2
    Params.append((10000,Voltages['R2']))       #This field extends to infinity

    i = 0
    while Params[i][0] < pos:
        i = i+1
    field = (Params[i][1] - Params[i-1][1]) / (Params[i][0] - Params[i-1][0])

    return (field,Params[i][0],Params[i-1][0]) #


def acceleration(field,mass):
    """
    Calculates the acceleration of a given mass in a given field if it is
    singly ionized
    Acceleration calculated as A = q*E/m, q = -e
    
    Args:
        field: The electrical field, given in V/cm
        mass: The mass in AMU
        
    Returns:
        The acceleration in m/s^2
    
    Raises:
        ...
    """
    elementary_charge = -1.602e-19
    force = field * elementary_charge * 100
    acc = force / (mass * 1.6605e-27)
    return acc

def flight_time(mass,pos=0):
    """
    Calculates total flight time of an ion of given mass
    Consider to change how this function optimizes for speed, there might
    be much better solutions...
    
    Args:
        mass: Mass of the ion
        pos: Initial position, default is mid between A1 and A2
        
    Returns:
        t: The actual flighttime
        values: Named list containing performance data: time, pos, speed
        
    Raises:
        ...
    """
    detected = False
    t = 0
    dT = dt = 1e-10
    v = dt # Just to make sure v is positive from the beginning
    i = 0
    valid_until = -100000
    values = {} #Named list containing various status-information
    values['time'] = values['speed'] = values['pos'] = []

    while not detected:
        Field = field(pos)
        E = Field[0]
        A = acceleration(Field[0],mass)
        if v>0:
            if pos > valid_until:
                valid_until = Field[1]
                dT = dt
            else:
                if ((pos+500*v*dT) < valid_until) & (E == 0):
                    dT = 50 * dt
        else:
            if pos < valid_until:
                valid_until = Field[2]
                dT = dt
            else:
                if (pos > 29) & (E == 0): # Going back in the field free region
                    dT = 50 * dt

        v = v + A*dT
        pos = pos + (v*dT) * 100 # pos is in cm
        t = t+dT
        i = i+1
        detected = ((pos>110.8) | ((pos < 37.167) & (v<0)))

        if (i%10 == 0): #Collects potentially usable performence data for every 10'th iteration
            values['time'].append(t*1e6)
            values['pos'].append(pos)
            values['speed'].append(v)

    return (t,values)

