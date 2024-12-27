from configparser import ConfigParser 
import shutil

config = ConfigParser()
#initialization of files according to ip.cfg

config.read('config/ip.cfg')


#replace in docker-compose-macvlan.yml
with open('docker-compose-template.yml') as f:
	newText=f.read().replace('$IpBackend', config['DEFAULT']['IpBackend'])
	newText=newText.replace('$IpDns', config['DEFAULT']['IpDns'])
	newText=newText.replace('$IpLB', config['DEFAULT']['IpLB'])
	newText=newText.replace('$IpFrontend1', config['DEFAULT']['IpFrontend1'])
	newText=newText.replace('$IpFrontend2', config['DEFAULT']['IpFrontend2'])
	newText=newText.replace('$IpMosquitto', config['DEFAULT']['IpMosquitto'])
	newText=newText.replace('$IpMysql', config['DEFAULT']['IpMysql'])
	newText=newText.replace('$IpPhpmyadmin', config['DEFAULT']['IpPhpmyadmin'])
	newText=newText.replace('$IntMacVlan', config['DEFAULT']['IntMacVlan'])
	newText=newText.replace('$IntBridge', config['DEFAULT']['IntBridge'])
	newText=newText.replace('$SubnetMacvlan', config['DEFAULT']['SubnetMacvlan'])
	newText=newText.replace('$IpRangeMacvlan', config['DEFAULT']['IpRangeMacvlan'])
	newText=newText.replace('$GatewayMacvlan', config['DEFAULT']['GatewayMacvlan'])
	
with open('docker-compose-auto.yml', "w") as f:
	f.write(newText)

#replace in config.php
with open('config/template/config-template.php') as f:
	newText=f.read().replace('$IpBackend', config['DEFAULT']['IpBackend'])
	newText=newText.replace('$IpMosquitto', config['DEFAULT']['IpMosquitto'])
	newText=newText.replace('$IpMysql', config['DEFAULT']['IpMysql'])
	newText=newText.replace('$UserPSM', config['DEFAULT']['UserPSM'])
	newText=newText.replace('$PasswordPSM', config['DEFAULT']['PasswordPSM'])
	newText=newText.replace('$IpPSM', config['DEFAULT']['IpPSM'])
	newText=newText.replace('$PolicyApiPSM', config['DEFAULT']['PolicyApiPSM'])
	
with open('config/config.php', "w") as f:
	f.write(newText)
	
#copy php config file for both frontend1 and frontend2	
shutil.copyfile('config/config.php', 'data/frontend1/EW-demo-frontend/config.php')
shutil.copyfile('config/config.php', 'data/frontend2/EW-demo-frontend/config.php')

#replace in db.pod1 (DNS)
with open('config/template/db.pod1.template') as f:
	newText=f.read().replace('$IpBackend', config['DEFAULT']['IpBackend'])
	newText=newText.replace('$IpDns', config['DEFAULT']['IpDns'])
	newText=newText.replace('$IpLB', config['DEFAULT']['IpLB'])
	newText=newText.replace('$IpFrontend1', config['DEFAULT']['IpFrontend1'])
	newText=newText.replace('$IpFrontend2', config['DEFAULT']['IpFrontend2'])
	newText=newText.replace('$IpMosquitto', config['DEFAULT']['IpMosquitto'])
	newText=newText.replace('$IpMysql', config['DEFAULT']['IpMysql'])
	
with open('config/dns/db.pod1', "w") as f:
	f.write(newText)
	
#replace in docker-compose-psm-update.yml
with open('docker-compose-psm-updater-template.yml') as f:
	newText=f.read().replace('$UserPSM', config['DEFAULT']['UserPSM'])
	newText=newText.replace('$PasswordPSM', config['DEFAULT']['PasswordPSM'])
	newText=newText.replace('$IpPSM', config['DEFAULT']['IpPSM'])

with open('docker-compose-psm-updater.yml', "w") as f:
	f.write(newText)
