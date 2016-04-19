
from __future__ import print_function

import os
from os.path import join

print("############################################################")

for item in os.listdir('.'):
    if not os.path.isdir(item):
        continue
    if item.startswith('dev') or item == 'sym-files2':
        print('dev or sym-file2', item)
        continue
    if not os.path.isfile(join(item, 'graphsettings.xml')):
        print('no graphsetting', item)
        continue
    if not os.path.islink(join(item, 'live.php')):
        print('has no live.php link', item)
        continue
    if os.path.islink(join(item, 'live_old.php')):
        continue

    print('YEAH', item)

    continue

    try:
        print('Making symlink', join(item, 'live_old.php'), '../sym-files2/live_old.php')
        os.symlink('../sym-files2/live_old.php', join(item, 'live_old.php'))
    except OSError:
        print('Improper rights for', item)



