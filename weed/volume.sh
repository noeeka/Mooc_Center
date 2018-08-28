#!/bin/bash
nohup ./weed -v 3 -log_dir "/data/weeds/volumeData/logs" volume  -port 9334 -dir "/data/weeds/volumeData/v1" -dataCenter=v1 -whiteList "127.0.0.1,192.168.1.231" >>./volume_v1_sfs.log &
