#!/bin/env python
#pylint: disable-msg=I0011,W0142

""" Script that will synchronize the stm metadata in the database to
that in the files
"""

import os
import sys
sys.path.insert(1, '/home/camp/stm312')
import json
from stm_parse import ParseMulFile
import stmimages_credentials
import MySQLdb

# stm312images


class UpdateSTMMetaData:
    """ Class that updates the MySQL database with metadata from new
    STM files
    """

    def __init__(self):
        # Get the time of the last sync
        self.last_sync = 222  # Should be parsed from file and be in epoc time
        self.files_basedir = '/u/data1/stm312/stm/Images'
        self.extension = '.mul'
        self.database = MySQLdb.connect(user=stmimages_credentials.USER,
                                  passwd=stmimages_credentials.PASS,
                                  charset='utf8',
                                  db='cinfdata')
        self.cursor = self.database.cursor()

    def get_file_changes(self):
        """ Return a list of files to upload """
        # _get_files and _get_files_in_db returns sets
        files_on_drive = self._get_files(self.files_basedir, set())
        files_in_db = self._get_files_in_db()
        # Form sets of files to upload to db
        files_to_update = files_on_drive - files_in_db
        files_to_delete_from_db = files_in_db - files_on_drive
        print '<h2>Comparing files from drive and in database</h2>'
        print '<p>New files to <b>add</b> to the database: {0}</p>'.\
            format(len(files_to_update))
        print ('<p title="The metadata for files that are in the database, '
               'but no longer on the drive, will be deleted from the '
               'database">Files to <b>delete</b> from the database: {0}</p>').\
            format(len(files_to_delete_from_db))
        return files_to_update, files_to_delete_from_db

    def run(self):
        """ Check if there is new metadata to upload and if so so it """
        upload, delete = self.get_file_changes()

        # Upload
        if len(upload) > 0:
            print '<h2>Uploading metadata for new files</h2>\n<table>'
        for file_ in upload:
            print '<tr><td>{0} .. DONE</td></tr>'.format(file_)
            absolute_path = os.path.join(self.files_basedir, file_)
            mul = ParseMulFile(absolute_path)
            mul.read_index()
            metadata = mul.read_all_metadata_raw()
            for image in metadata:
                label = self._prepare_label(image['label'], file_,
                                            absolute_path)

                # .keys() and .values() are garantied to give same sorting:
                #http://docs.python.org/2/library/
                #stdtypes.html#mapping-types-dict
                columns = ', '.join([str(key) for key in label.keys()])
                values = ', '.join([str(val) for val in label.values()])
                query = 'INSERT into {0} ( {1} ) VALUES ( {2} )'.\
                    format('stm312_stmimages', columns, values)
                self.cursor.execute(query)
        if len(upload) > 0:
            print '</table>'

        # Delete
        if len(delete) > 0:
            print '<h2>Deleting metadata for removed files</h2>\n<table>'
        for file_ in delete:
            query = ('delete from stm312_stmimages where relative_path = '
                     '"{0}"').format(file_)
            self.cursor.execute(query)
            print '<tr><td>{0} .. DONE</td></tr>'.format(file_)
        if len(delete) > 0:
            print '</table>'

        if len(upload) + len(delete) == 0:
            print ('<p><a href="http://www.youtube.com/watch?v=G-C1dRZ_hps">'
                   '"Another job well done ..!"</a></p>')

    def _prepare_label(self, label, file_, absolute_path):
        """ Prepare the label for database upload """
        # Modify metadata
        label['relative_path'] = file_
        label['absolute_path'] = absolute_path
        # Replace individual datetime components with datetime string
        label['time'] = '{year}-{month:02d}-{day:02d} '\
            '{hour:02d}:{minute:02d}:{second:02d}'.format(**label)
        for key in ['year', 'month', 'day', 'hour', 'minute',
                    'second']:
            del label[key]
        # Store the spare dict as json
        label['spare'] = json.dumps(label['spare'])

        for key, value in label.items():
            if type(value) is str:
                label[key] = '\'{0}\''.format(
                    self.database.escape_string(value))
            elif type(value) is unicode:
                label[key] = '\'{0}\''.format(
                    self.database.escape_string(value.encode('utf-8')))
        return label

    def _get_files(self, current_path, paths):
        """ Recursively search the basedir and return a list of all MUL-files
        in it
        """
        try:
            files = os.listdir(current_path)
            files.sort()
            for filename in files:
                full_path = os.path.join(current_path, filename)
                if os.path.isdir(full_path):
                    paths = self._get_files(full_path, paths)
                else:
                    if os.path.splitext(filename)[1].lower() == self.extension:
                        paths.add(
                            os.path.relpath(full_path, self.files_basedir)
                            )

        except OSError:
            print 'Access Denied: ' + current_path

        return paths

    def _get_files_in_db(self):
        """ Get a list of all the files in the db """
        query = 'select relative_path from stm312_stmimages'
        self.cursor.execute(query)
        out = set()
        for element in self.cursor.fetchall():
            out.add(element[0].encode('utf-8'))
        return out


if __name__ == '__main__':
    UPDATER = UpdateSTMMetaData()
    UPDATER.run()
