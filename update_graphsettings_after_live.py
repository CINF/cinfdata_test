
from __future__ import print_function

import sys
import codecs
from xml.etree import ElementTree as ET

path = sys.argv[1]

xml = ET.parse(path)

root = xml.getroot() 
for element in root:
    if element.tag == 'global_settings':
        break

sockets = {}
for socket_element in root.find('.//sockets'):
    sockets[socket_element.tag] = socket_element.text.split(':')[0]
print('Found sockets:', sockets)

with codecs.open(path, mode='r', encoding='utf-8') as file_:
    lines = file_.readlines()

in_container = False
in_plot = False
in_sockets = False
lines_out = []
socket_num = None


def find_socket_num(num):
    for line_ in lines[num:]:
        if line_.find('<socket>') >= 0 and line_.find('</socket>') >= 0:
            return line_.split('>')[1].split('<')[0]
        


for num, line in enumerate(lines):
    if line.find('<container') >= 0 and line.find('<containers') < 0:
        in_container = True
        print('\n######## In container', line.strip())
    if line.find('</container') >= 0  and line.find('</containers') < 0:
        print('######## Closed container', line.strip())
        in_container = False

    if in_container and (line.find('<plot') >= 0 or line.find('<item') >= 0):
        in_plot = True
        socket_num = find_socket_num(num)
        print('### In plot/item', line.strip(), 'socket id', socket_num)
    if in_container and (line.find('</plot') >= 0 or line.find('</item') >= 0):
        print('### Closed plot/item', line.strip())
        in_plot = False
        socket_num = None

    if '<sockets>' in line:
        in_sockets = True
        print('\n######## In sockets', line.strip())
        continue

    if '</sockets>' in line:
        in_sockets = False
        print('\n######## Closed sockets', line.strip())
        continue

    if in_sockets:
        print('Deleting socket def type', line.strip())
        continue

    if in_container:
        if line.find('<type>figure</type>') >= 0:
            print('* Replace', '<type>figure</type>', '<type>date_figure</type>')
            lines_out.append(line.replace('<type>figure</type>', '<type>date_figure</type>'))
            continue

        if line.find('<update_interval>') >= 0:
            print('* Deleted update_interval line', line.strip())
            continue

        if '<type>data</type>' in line:
            space = line.split('<')[0]
            lines_out.append(space + '<type>table</type>\n')
            print('* Replace', '<type>data</type>', '<type>table</type>')
            continue


    if in_container and in_plot:
        if line.find('<socket>') >= 0:
            print('* Delete socket line', line.strip())
            continue
        if line.find('<id') >= 0:
            space = line.split('<')[0]
            id_ = line.split('>')[1].split('<')[0]
            socket = sockets['socket' + socket_num]
            new_line = space + '<data_channel>{0}:{1}</data_channel>\n'.format(socket, id_)
            lines_out.append(new_line)
            print('* Replace id line with data_channel', line.strip(), new_line.strip())
            continue
    
    lines_out.append(line)

# Write data out
outpath = path + '_new'
with codecs.open(outpath, 'w', 'utf-8') as file_:
    file_.writelines(lines_out)

# Check result
print('\nParsed:', path)
print('Wrote to:', outpath)
print('Attempt to parse the new file', end='')
try:
    ET.parse(outpath)
    print(' ... SUCCESSFUL')
except:
    print(' ... FAILED. Complain to the script author')
else:
    print('The convertion was successful. Please inspect the changes with:')
    print('colordiff -u {0} {1}|less -R'.format(path, outpath))
    print('If they look good move the new file into place')
