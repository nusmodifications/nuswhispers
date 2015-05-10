#!/bin/bash
# Adds the Laravel cronjob entry to run every minute
# Based on: http://stackoverflow.com/questions/29382385/how-do-i-set-up-a-vagrant-box-to-always-have-a-cron-job

cron="*/1 * * * * php /home/vagrant/sites/nuswhispers/artisan schedule:run"

# Escape all the asterisks so we can grep for it
cron_escaped=$(echo "$cron" | sed s/\*/\\\\*/g)

# Check if cron job already in crontab
crontab -l | grep "${cronescaped}"
if [[ $? -eq 0 ]] ;
  then
    echo "Crontab already exists. Exiting..."
    exit
  else
    # Write out current crontab into temp file
    crontab -l > mycron
    # Append new cron into cron file
    echo "$cron" >> mycron
    # Install new cron file
    crontab mycron
    # Remove temp file
    rm mycron
fi