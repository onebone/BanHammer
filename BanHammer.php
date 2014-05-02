<?php

/*
__PocketMine Plugin__
name=BanHammer
version=1.0
author=onebone
apiversion=13
class=BanHammer
*/

class BanHammer implements Plugin{
	private $api;
	
	public function __construct(ServerAPI $api, $server = false){
		$this->api = $api;
	}
	
	public function init(){
		$this->config = $this->api->plugin->readYAML($this->api->plugin->createConfig($this, array(
			"ban-hammer" => 280,
			"kick-hammer" => 267,
			"banip-hammer" => 292
		))."config.yml");
		DataPacketReceiveEvent::register(array($this, "packetHandler"));
	}
	
	public function __destruct(){}
	
	public function packetHandler(DataPacketReceiveEvent $event){
		$packet = $event->getPacket();
		if($packet instanceof InteractPacket){
			$issuer = $event->getPlayer();
			$target = $this->api->entity->get($packet->target);
			if($target instanceof Entity and $target->player instanceof Player){
				if($this->api->ban->isOp($issuer->iusername)){
					$slot = $issuer->getSlot($issuer->slot);
					switch($slot->getID()){
						case $this->config["ban-hammer"]:
						$banList = new Config(DATA_PATH."banned.txt", CONFIG_LIST); // To ban precise player
						$banList->set($target->player->iusername);
						$banList->save();
						$this->api->chat->broadcast($target->player->username." has been banned by ".$issuer->username);
						$target->player->close("harmed by ban hammer");
						$this->api->ban->commandHandler("ban", array("reload"), "plugin", false);
						break;
						case $this->config["kick-hammer"]:
						$this->api->chat->broadcast($target->player->username." has been kicked by ".$issuer->username);
						$target->player->close("harmed by kick hammer");
						break;
						case $this->config["banip-hammer"]:
						$banList = new Config(DATA_PATH."banned-ips.txt", CONFIG_LIST);
						$banList->set($target->player->ip);
						$banList->save();
						$issuer->sendChat($target->player->username." (".$target->player->ip.") has been banned");
						$target->player->close("harmed by ban IP hammer");
						$this->api->ban->commandHandler("banip", array("reload"), "plugin", false);
						break;
					}
				}
			}
		}
	}
}