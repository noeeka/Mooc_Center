#!/bin/bash
nohup ./weed -log_dir=/data/weeds/masterData/logs  master -mdir=/data/weeds/masterData/data -port=9333 -whiteList="127.0.0.1,192.168.1.231" >> ./server_sfs.log &
