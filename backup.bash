#!/bin/bash

tar zcvf "backup/`date`.tar.gz" --exclude 'backup' --exclude 'backup/*' --exclude 'figures' --exclude 'figures/*' *
