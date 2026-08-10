<?php

namespace raklib\server;

require_once __DIR__ . '/SessionManagerRuntimeConfig.php';

use raklib\Binary;
use raklib\protocol\ACK;
use raklib\protocol\ADVERTISE_SYSTEM;
use raklib\protocol\DATA_PACKET_0;
use raklib\protocol\DATA_PACKET_1;
use raklib\protocol\DATA_PACKET_2;
use raklib\protocol\DATA_PACKET_3;
use raklib\protocol\DATA_PACKET_4;
use raklib\protocol\DATA_PACKET_5;
use raklib\protocol\DATA_PACKET_6;
use raklib\protocol\DATA_PACKET_7;
use raklib\protocol\DATA_PACKET_8;
use raklib\protocol\DATA_PACKET_9;
use raklib\protocol\DATA_PACKET_A;
use raklib\protocol\DATA_PACKET_B;
use raklib\protocol\DATA_PACKET_C;
use raklib\protocol\DATA_PACKET_D;
use raklib\protocol\DATA_PACKET_E;
use raklib\protocol\DATA_PACKET_F;
use raklib\protocol\EncapsulatedPacket;
use raklib\protocol\NACK;
use raklib\protocol\OPEN_CONNECTION_REPLY_1;
use raklib\protocol\OPEN_CONNECTION_REPLY_2;
use raklib\protocol\OPEN_CONNECTION_REQUEST_1;
use raklib\protocol\OPEN_CONNECTION_REQUEST_2;
use raklib\protocol\Packet;
use raklib\protocol\UNCONNECTED_PING;
use raklib\protocol\UNCONNECTED_PONG;
use raklib\RakLib;

class SessionManager
{
  /**
   * ============================================
   * CONFIGURAÇÕES DE PROTEÇÃO DDOS
   * ============================================
   * ENABLE_DDOS_PROTECTION: 1 = ATIVADO | 0 = DESATIVADO
   *   - Ativa/Desativa TODAS as proteções DDoS
   */
  const ENABLE_DDOS_PROTECTION = 1;

  /**
   * USE_PERMANENT_BLOCK: 1 = BLOQUEIO PERMANENTE | 0 = BLOQUEIO COM CONTAGEM REGRESSIVA
   *   - Se 1: IPs bloqueados permanecem bloqueados para sempre (sem lag de armazenamento de tempo)
   *   - Se 0: IPs bloqueados são automaticamente desbloqueados após o tempo especificado
   */
  const USE_PERMANENT_BLOCK = 1;

  /**
   * ============================================
   * CONFIGURAÇÕES DE LOGGING DE IPs
   * ============================================
   * ENABLE_IP_LOGGING: 1 = ATIVADO | 0 = DESATIVADO
   *   - Mostra todos os IPs que enviam/recebem dados em tempo real no console
   *   - Registra o IP, porta e tamanho do pacote
   */
  const ENABLE_IP_LOGGING = 0;

  /**
   * ============================================
   * FIM DAS CONFIGURAÇÕES
   * ============================================
   */

  protected $packetPool = [],
  $server,
  $socket,
  $receiveBytes = 0,
  $sendBytes = 0,
  $sessions = [],
  $name = "",
  $packetLimit = 200,
  $shutdown = false,
  $ticks = 0,
  $lastMeasure,
  $block = [],
  $ipSec = [];

  private $configPath;
  private $configLastCheckedAt = 0.0;
  private $configReloadIntervalSeconds = 1;
  private $protectionEnabled = true;
  private $activeMode = 'anti_ddos';
  private $lastConfigSignature = null;
  
  private $packet1Counters = [];

  private $blockedIPsFile = 'ip_blocklist.txt';

  public $portChecking = true;
  
  private $ipBytesCounters = [];
    
  private $ipWarnCounters = [];

  private $blockedCount = 0;
  
  private $blockedIPs = [];

  private $loggedIPs = [];

  public function __construct(RakLibServer $server, UDPServerSocket $socket) {
    $this->server = $server;
    $this->socket = $socket;
    $this->configPath = __DIR__ . DIRECTORY_SEPARATOR . 'session_manager_hot_reload.json';
    $this->reloadRuntimeConfig();
    $this->registerPackets();

    $this->serverId = mt_rand(0, PHP_INT_MAX);
    
    $this->initializeBlockedIPs();
    $this->run();
  }

  public function getPort() {
    return $this->server->getPort();
  }

  public function getLogger() {
    return $this->server->getLogger();
  }

  public function run() {
    $this->tickProcessor();
  }

  private function reloadRuntimeConfigIfNeeded() {
    $now = microtime(true);
    if ($this->configReloadIntervalSeconds > 0 && $this->configLastCheckedAt > 0 && ($now - $this->configLastCheckedAt) < $this->configReloadIntervalSeconds) {
      return;
    }

    $this->configLastCheckedAt = $now;
    $this->reloadRuntimeConfig();
  }

  private function reloadRuntimeConfig() {
    $config = SessionManagerRuntimeConfig::load($this->configPath);
    $this->activeMode = $config['mode'];
    $this->protectionEnabled = (bool) $config['protection_enabled'];
    $this->configReloadIntervalSeconds = max(1, (int) $config['reload_interval_seconds']);

    $signature = $this->activeMode . '|' . ($this->protectionEnabled ? '1' : '0') . '|' . $this->configReloadIntervalSeconds;
    if ($this->lastConfigSignature !== $signature) {
      $this->lastConfigSignature = $signature;
      $this->getLogger()->notice("§6Modo de sessão alterado para {$this->activeMode} (proteção: " . ($this->protectionEnabled ? 'ativada' : 'desativada') . ")");
    }
  }

  private function isProtectionEnabled() {
    return $this->protectionEnabled;
  }
  
  /*private function tickProcessor(){
    $this->lastMeasure = microtime(true);

    while(!$this->shutdown or count($this->sessions) > 0){
      $start = microtime(true);

      /*
       * O código abaixo foi projetado para permitir a cooperação entre o envio e o recebimento para evitar a lentidão de qualquer um deles
       * quando o tráfego intenso está vindo de qualquer maneira. A rendição ocorrerá após 100 mensagens.
       *
      do{
        $stream = !$this->shutdown;
        for($i = 0; $i < 100 && $stream && !$this->shutdown; ++$i){
          $stream = $this->receiveStream();
        }

        $socket = true;
        for($i = 0; $i < 100 && $socket; ++$i){
          $socket = $this->receivePacket();
        }
      }while($stream || $socket);

      $this->tick();

      $time = microtime(true) - $start;
      if ($time < 0.05) {
        @time_sleep_until(microtime(true) + 0.05 - $time);
      }
    }

    $this->socket->close();
  }*/
      
      private function tickProcessor(){
    $this->lastMeasure = microtime(true);

    while(!$this->shutdown){
        $start = microtime(true);
        $max = 5000;
        
        // Drena todos os pacotes UDP acumulados
        while(--$max and $this->receivePacket()) ;
        while($this->receiveStream()) ;

        $this->tick();

        $time = microtime(true) - $start;

        // Meta: manter cada ciclo rodando no tempo limite de 1.5ms (0.0015s)
        // Isso reduz o atraso/ping para no máximo 1.5ms sem forçar 100% de uso de CPU.
        $targetTime = 0.0015;

        if($time < $targetTime){
            // usleep faz a thread pausar em microssegundos
            usleep((int)(($targetTime - $time) * 1000000));
        }
    }
}

  private function tick() {
    $this->reloadRuntimeConfigIfNeeded();

    $time = microtime(true);
    foreach ($this->sessions as $session) {
      $session->update($time);

      if (($this->ticks % 20) == 0) {
        $this->streamPing($session);
      }
    }

    $this->ipSec = [];

    if (($this->ticks % 20) === 0) {
        $diff = max(0.005, $time - $this->lastMeasure);

        $bandwidthData = [
            "up" => $this->sendBytes / $diff,
            "down" => $this->receiveBytes / $diff
        ];
        $this->streamOption("bandwidth", serialize($bandwidthData));

        // --- PROTEÇÃO: BLOQUEIO GLOBAL POR 1MB/s ---
        if ($this->isProtectionEnabled()) {
          $totalTraffic = $this->sendBytes + $this->receiveBytes;
          if ($totalTraffic >= 1048576) { // 1MB = 1.048.576 bytes
              exec("sudo iptables -A INPUT -p udp --destination-port " . $this->server->getPort() . " -j DROP", $output);
              $this->getLogger()->notice("§cBloqueio global ativado: tráfego total >= 1MB/s");
          }
        }
        // --- FIM: BLOQUEIO GLOBAL POR 1MB/s ---

        $this->lastMeasure = $time;
        $this->sendBytes = 0;
        $this->receiveBytes = 0;

        // --- LIMPEZA DE IPs BLOQUEADOS (APENAS SE BLOQUEIO COM CONTAGEM REGRESSIVA) ---
        if (self::USE_PERMANENT_BLOCK === 0 && $this->isProtectionEnabled()) {
          $addressesToRemove = [];
          $now = microtime(true);
          foreach ($this->block as $address => $timeout) {
              if ($timeout <= $now) {
                  $addressesToRemove[] = $address;
              } else {
                  break;
              }
          }

          foreach ($addressesToRemove as $address) {
              unset($this->block[$address]);
          }
        }
        // --- FIM: LIMPEZA DE IPs BLOQUEADOS ---

        $this->checkMaxSessions();
    }

    ++$this->ticks;

    // --- LIMPEZA: LIMPAR IPs LOGADOS PERIODICAMENTE ---
    if (self::ENABLE_IP_LOGGING === 1 && ($this->ticks % 600) === 0) {
      $this->loggedIPs = [];
      $this->getLogger()->debug("§6Cache de IPs logados foi limpo");
    }
    // --- FIM: LIMPEZA: LIMPAR IPs LOGADOS PERIODICAMENTE ---

    // Limpa contadores antigos de bytes (opcional)
    $now = time();
    foreach ($this->ipBytesCounters as $ip => $data) {
        if ($data['timestamp'] !== $now) {
            unset($this->ipBytesCounters[$ip]);
        }
    }
  }
  
  private function receivePacket()
  {
    $len = $this->socket->readPacket($buffer, $source, $port);

    // --- LOGGING: MOSTRAR IPs EM TEMPO REAL ---
    if (self::ENABLE_IP_LOGGING === 1) {
      if (!isset($this->loggedIPs[$source . ":" . $port])) {
          $this->getLogger()->info("§a[IP CONECTADO] $source:$port | Pacote: " . $len . " bytes");
          $this->loggedIPs[$source . ":" . $port] = true;
      }
    }
    // --- FIM: LOGGING: MOSTRAR IPs EM TEMPO REAL ---
    if ($this->isProtectionEnabled()) {
      $now = time();
      if (!isset($this->ipBytesCounters[$source])) {
          $this->ipBytesCounters[$source] = [
              'bytes' => $len,
              'timestamp' => $now
          ];
      } else {
          if ($this->ipBytesCounters[$source]['timestamp'] === $now) {
              $this->ipBytesCounters[$source]['bytes'] += $len;
          } else {
              $this->ipBytesCounters[$source]['bytes'] = $len;
              $this->ipBytesCounters[$source]['timestamp'] = $now;
          }
      }

      // Se passar de 30KB/s, bloqueia o IP
      if ($this->ipBytesCounters[$source]['bytes'] >= 30000) {
          $bytes = $this->ipBytesCounters[$source]['bytes'];
          $now = time();
          if (!isset($this->ipWarnCounters[$source])) {
              $this->ipWarnCounters[$source] = [
                  'count' => 1,
                  'first' => $now
              ];
          } else {
              if ($now - $this->ipWarnCounters[$source]['first'] <= 10) {
                  $this->ipWarnCounters[$source]['count']++;
              } else {
                  // Reset se passou de 10 segundos
                  $this->ipWarnCounters[$source]['count'] = 1;
                  $this->ipWarnCounters[$source]['first'] = $now;
              }
          }

          if ($this->ipWarnCounters[$source]['count'] >= 1) {
              $this->blockAddress($source, 300);
              $this->getLogger()->notice("§cIP bloqueado por exceder 30KB/s 1 vezes em 10s: $source ({$bytes} bytes/s)");
              exec("sudo iptables -D INPUT -s $source -p udp --destination-port " . $this->server->getPort() . " -j ACCEPT", $output);
              exec("sudo iptables -A INPUT -s $source -p udp --destination-port " . $this->server->getPort() . " -j DROP", $output);
              // Guarda o IP bloqueado
              $this->blockedIPs[] = $source;

              // Se já tiver 5 IPs diferentes bloqueados, bloqueia todos os IPs
              if (count($this->blockedIPs) >= 2) {
                  $this->getLogger()->emergency("§4Mais de 2 IPs bloqueados individualmente — bloqueando todos os IPs!");

                  // Bloqueia tudo no iptables para porta do servidor
                  exec("sudo iptables -A INPUT -p udp --destination-port " . $this->server->getPort() . " -j DROP", $output);

                  // Se quiser limpar a lista depois do bloqueio geral
                  $this->blockedIPs = [];
              }
              unset($this->ipWarnCounters[$source]);
          } else {
              $this->getLogger()->notice("§eAviso {$this->ipWarnCounters[$source]['count']}/1: IP $source excedeu 30KB/s ({$bytes} bytes/s)");
          }

          unset($this->ipBytesCounters[$source]);
          return true;
      }
    }
    // --- FIM: PROTEÇÃO: CONTROLE DE BYTES POR IP ---

    if ($buffer === null) {
        return false;
    }
    
    if (isset($this->block[$source])) {
        return true;
    }

    // --- PROTEÇÃO: LIMITE DE PPS (PACOTES POR SEGUNDO) ---
    if ($this->isProtectionEnabled()) {
      if (isset($this->ipSec[$source])) {
          if (($this->ipSec[$source]++) >= $this->packetLimit) {
              $this->blockAddress($source);
              $this->getLogger()->notice("§aIP bloqueado devido o limite de pps: $source");
              return true;
          }
      } else {
          $this->ipSec[$source] = 1;
      }

      // --- PROTEÇÃO: BLOQUEIO POR PACOTES GRANDES ---
      /*if ($len >= 200) {
          $this->blockAddress($source);
          $this->getLogger()->notice("§aIP bloqueado devido a pacotes grandes: $source");
          return true;
      }*/
    } else {
      if (isset($this->ipSec[$source])) {
          $this->ipSec[$source]++;
      } else {
          $this->ipSec[$source] = 1;
      }
    }

    $pid = ord($buffer[0]);

    // --- PROTEÇÃO: BLOQUEIO DE PACOTES 0x1 ---
    if ($this->isProtectionEnabled()) {
      if ($pid === 0x1) {
          $now = time();
          if (!isset($this->packet1Counters[$source])) {
              $this->packet1Counters[$source] = [
                  'count' => 1,
                  'timestamp' => $now
              ];
          } else {
              if ($this->packet1Counters[$source]['timestamp'] === $now) {
                  $this->packet1Counters[$source]['count']++;
              } else {
                  $this->packet1Counters[$source]['count'] = 1;
                  $this->packet1Counters[$source]['timestamp'] = $now;
              }
          }

          if ($this->packet1Counters[$source]['count'] >= 30) {
              $this->blockAddress($source, 300);
              $this->getLogger()->notice("§cIP bloqueado por enviar 30 ou mais pacotes 0x1 em 1 segundo: $source");
              unset($this->packet1Counters[$source]);
              return true;
          }
      }
    }
    // --- FIM: PROTEÇÃO: BLOQUEIO DE PACOTES 0x1 ---

    if ($buffer !== null) {
      $this->receiveBytes += $len;
      if (isset($this->block[$source])) {
        return true;
      }

      if (isset($this->ipSec[$source])) {
        $this->ipSec[$source]++;
      } else {
        $this->ipSec[$source] = 4;
      }

      if ($len > 0) {
        $pid = ord($buffer[0]);

        if ($pid === UNCONNECTED_PING::$ID) {
          // No need to create a session for just pings
          $packet = new UNCONNECTED_PING;
          $packet->buffer = $buffer;
          $packet->decode();
          $pk = new UNCONNECTED_PONG();
          $pk->serverID = $this->getID();
          $pk->pingID = $packet->pingID;
          $pk->serverName = $this->getName();
          $this->sendPacket($pk, $source, $port);
        } elseif ($pid === UNCONNECTED_PING::$ID) {
          // Ignore it...
        } elseif (($packet = $this->getPacketFromPool($pid)) !== null) {
          $packet->buffer = $buffer;
          $this->getSession($source, $port)->handlePacket($packet);
        } else {
          $this->streamRaw($source, $port, $buffer);
        }
      }
      return true;
    }

    return false;
  }

  public function sendPacket(Packet $packet, $dest, $port) {
    $packet->encode();
    $encodedPacket = $packet->buffer;
    $maxPacketSize = 99999999;

    if (strlen($encodedPacket) > $maxPacketSize || strlen($encodedPacket) == 440 || strlen($encodedPacket) < 1) {
        return false;
    }
        
    $this->sendBytes += $this->socket->writePacket($encodedPacket, $dest, $port);
  }

  public function streamEncapsulated(Session $session, EncapsulatedPacket $packet, $flags = RakLib::PRIORITY_NORMAL) {
    $id = $session->getAddress() . ":" . $session->getPort();
    $buffer = chr(RakLib::PACKET_ENCAPSULATED) . chr(strlen($id)) . $id . chr($flags) . $packet->toBinary(true);
    $this->server->pushThreadToMainPacket($buffer);
  }

  public function streamRaw($address, $port, $payload) {
    if (isset($this->block[$address])) {
        return false;
    }
    
    if (!$this->isProtectionEnabled()) {
        return; // Proteção desativada, não faz verificação
    }
    
    if (substr($payload, 0, 2) !== "\xfe\xfd") {
      $this->blockAddress($address);
      $this->getLogger()->notice("§aIP bloqueado devido a pacotes falsos: $address");
      return false;
    }
    $packetLength = strlen($payload);
    if ($packetLength >= 30){
      return false;
    }
    $buffer = chr(RakLib::PACKET_RAW) . chr(strlen($address)) . $address . Binary::writeShort($port) . $payload;
    $this->server->pushThreadToMainPacket($buffer);
  }

  protected function streamPing(Session $session) {
    $identifier = $session->getAddress() . ":" . $session->getPort();
    $ping = $session->getPing();

    $buffer = chr(RakLib::PACKET_PING) . chr(strlen($identifier)) . $identifier . chr(strlen($ping)) . $ping;
    $this->server->pushThreadToMainPacket($buffer);
  }

  protected function streamClose($identifier, $reason) {
    $buffer = chr(RakLib::PACKET_CLOSE_SESSION) . chr(strlen($identifier)) . $identifier . chr(strlen($reason)) . $reason;
    $this->server->pushThreadToMainPacket($buffer);
  }

  protected function streamInvalid($identifier) {
    $buffer = chr(RakLib::PACKET_INVALID_SESSION) . chr(strlen($identifier)) . $identifier;
    $this->server->pushThreadToMainPacket($buffer);
  }

  protected function streamOpen(Session $session) {
    $identifier = $session->getAddress() . ":" . $session->getPort();
    $buffer = chr(RakLib::PACKET_OPEN_SESSION) . chr(strlen($identifier)) . $identifier . chr(strlen($session->getAddress())) . $session->getAddress() . Binary::writeShort($session->getPort()) . Binary::writeLong($session->getID());
    $this->server->pushThreadToMainPacket($buffer);
  }

  protected function streamACK($identifier, $identifierACK) {
    $buffer = chr(RakLib::PACKET_ACK_NOTIFICATION) . chr(strlen($identifier)) . $identifier . Binary::writeInt($identifierACK);
    $this->server->pushThreadToMainPacket($buffer);
  }

  protected function streamOption($name, $value) {
    $buffer = chr(RakLib::PACKET_SET_OPTION) . chr(strlen($name)) . $name . $value;
    $this->server->pushThreadToMainPacket($buffer);
  }
 
  private function checkSessions() {
    $sessionCount = count($this->sessions);
    if ($sessionCount > 4096) {
        $sessionsToRemove = [];
        foreach ($this->sessions as $i => $s) {
            if ($s->isTemporal()) {
                $sessionsToRemove[] = $i;
                if (count($sessionsToRemove) <= 4096) {
                    break;
                }
            }
        }

        if (!empty($sessionsToRemove)) {
            $this->sessions = array_values(array_diff_key($this->sessions, array_flip($sessionsToRemove)));
        }
    }
  }
  
  /*private function checkSessions() {
    if (count($this->sessions) > 4096) {
        foreach ($this->sessions as $i => $s) {
            if ($s->isTemporal()) {
                unset($this->sessions[$i]);
                if (count($this->sessions) <= 4096) {
                    break;
                }
            }
        }
    }
  }*/
  
  private function checkMaxSessions() {
    if (!$this->isProtectionEnabled()) {
        return; // Proteção desativada, não faz verificação
    }

    $maxSessionsPerIP = 4;

    $ipCount = [];
    
    foreach ($this->sessions as $session) {
        $ip = $session->getAddress();
        
        if (!isset($ipCount[$ip])) {
            $ipCount[$ip] = 1;
        } else {
            $ipCount[$ip]++;
            if ($ipCount[$ip] >= $maxSessionsPerIP) {
                $this->blockAddress($ip);
                $this->getLogger()->notice("§aIP bloqueado devido a tentativa de ataque a bot: $ip");
            }
        }
    }
  }

  public function receiveStream() {
    if (strlen($packet = $this->server->readMainToThreadPacket()) > 0) {
      $id = ord($packet[0]);
      $offset = 1;
      if ($id === RakLib::PACKET_ENCAPSULATED) {
        $len = ord($packet[$offset++]);
        $identifier = substr($packet, $offset, $len);
        $offset += $len;
        if (isset($this->sessions[$identifier])) {
          $flags = ord($packet[$offset++]);
          $buffer = substr($packet, $offset);
          $this->sessions[$identifier]->addEncapsulatedToQueue(EncapsulatedPacket::fromBinary($buffer, true), $flags);
        } else {
          $this->streamInvalid($identifier);
        }
      } elseif ($id === RakLib::PACKET_RAW) {
        $len = ord($packet[$offset++]);
        $address = substr($packet, $offset, $len);
        $offset += $len;
        $port = Binary::readShort(substr($packet, $offset, 2));
        $offset += 2;
        $payload = substr($packet, $offset);
        $this->socket->writePacket($payload, $address, $port);
      } elseif ($id === RakLib::PACKET_CLOSE_SESSION) {
        $len = ord($packet[$offset++]);
        $identifier = substr($packet, $offset, $len);
        if (isset($this->sessions[$identifier])) {
          $this->removeSession($this->sessions[$identifier]);
        } else {
          $this->streamInvalid($identifier);
        }
      } elseif ($id === RakLib::PACKET_INVALID_SESSION) {
        $len = ord($packet[$offset++]);
        $identifier = substr($packet, $offset, $len);
        if (isset($this->sessions[$identifier])) {
          $this->removeSession($this->sessions[$identifier]);
        }
      } elseif ($id === RakLib::PACKET_SET_OPTION) {
        $len = ord($packet[$offset++]);
        $name = substr($packet, $offset, $len);
        $offset += $len;
        $value = substr($packet, $offset);
        switch ($name) {
          case "name":
            $this->name = $value;
            break;
          case "portChecking":
            $this->portChecking = (bool)$value;
            break;
          case "packetLimit":
            $this->packetLimit = (int)$value;
            break;
        }
      } elseif ($id === RakLib::PACKET_BLOCK_ADDRESS) {
        $len = ord($packet[$offset++]);
        $address = substr($packet, $offset, $len);
        $offset += $len;
        $timeout = Binary::readInt(substr($packet, $offset, 4));
        $this->blockAddress($address, $timeout);
      } elseif ($id === RakLib::PACKET_UNBLOCK_ADDRESS) {
        $len = ord($packet[$offset++]);
        $address = substr($packet, $offset, $len);
        $offset += $len;
        $this->unblockAddress($address);
      } elseif ($id === RakLib::PACKET_SHUTDOWN) {
        foreach ($this->sessions as $session) {
          $this->removeSession($session);
        }

        $this->socket->close();
        $this->shutdown = true;
      } elseif ($id === RakLib::PACKET_EMERGENCY_SHUTDOWN) {
        $this->shutdown = true;
      } else {
        return false;
      }

      return true;
    }

    return false;
  }

  public function blockAddress($address, $timeout = 300){
    // Se proteções DDoS estão desativadas, não bloqueia
    if (!$this->isProtectionEnabled()) {
        return;
    }

    // Define o tempo de bloqueio
    if (self::USE_PERMANENT_BLOCK === 1) {
        // Bloqueio permanente
        $final = PHP_INT_MAX;
    } else {
        // Bloqueio com contagem regressiva
        $final = microtime(true) + $timeout;
    }
    
    if(!isset($this->block[$address]) or $timeout === -1){
        if($timeout === -1){
            $final = PHP_INT_MAX;
        } else {
            $this->getLogger()->notice("§6Anti-DDoS IP atacando: §e $address");

            if ($this->blockedCount < 5) {
               exec("sudo iptables -D INPUT -s $address -p udp --destination-port " . $this->server->getPort() . " -j ACCEPT", $output);
               exec("sudo iptables -A INPUT -s $address -p udp --destination-port " . $this->server->getPort() . " -j DROP", $output);
            } else {
                exec("sudo iptables -A INPUT -p udp --destination-port " . $this->server->getPort() . " -j DROP", $output);
                $this->getLogger()->notice("§cTodos os IPs foram bloqueados devido a ataque em massa.");
            }

            $this->blockedCount++;
            $this->getLogger()->notice("Resposta do firewall: " . implode("\n", $output));

            $d = date("m.d.y H:i:s");
            $ab = @fopen("RakLib.log", "a+");
            fwrite($ab, "\n$address");
            fclose($ab);
        }

        $this->block[$address] = $final;
    } elseif($this->block[$address] < $final){
        $this->block[$address] = $final;
    }
  }

  public function unblockAddress($address)
  {
    unset($this->block[$address]);
             
    exec("sudo iptables -D INPUT -s $address -p udp --destination-port " . $this->server->getPort() . " -j DROP", $output);
    $this->getLogger()->notice("Resposta do firewall: " . $output);
  }

  public function initializeBlockedIPs()
  {
     
  }

  public function getSession($ip, $port) {
    $id = $ip . ":" . $port;

    if (isset($this->block[$ip])) {
      return null;
    }

    if (!isset($this->sessions[$id])) {
      $this->checkSessions();
      $this->checkMaxSessions();
      $this->sessions[$id] = new Session($this, $ip, $port);
    }

    return $this->sessions[$id];
  }

  public function removeSession(Session $session, $reason = "unknown") {
    $id = $session->getAddress() . ":" . $session->getPort();
    if (isset($this->sessions[$id])) {
      $this->sessions[$id]->close();
      unset($this->sessions[$id]);
      $this->streamClose($id, $reason);
    }
  }

  public function openSession(Session $session) {
    $this->streamOpen($session);
  }

  public function notifyACK(Session $session, $identifierACK) {
    $this->streamACK($session->getAddress() . ":" . $session->getPort(), $identifierACK);
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function getID() {
    return $this->serverId;
  }

  private function registerPacket($id, $class) {
    $this->packetPool[$id] = new $class;
  }

  /**
  * @param $id
  *
  * @return Packet
  */
  public function getPacketFromPool($id) {
    if (isset($this->packetPool[$id])) {
      return clone $this->packetPool[$id];
    }

    return null;
  }

  private function registerPackets() {
    $this->registerPacket(OPEN_CONNECTION_REQUEST_1::$ID, OPEN_CONNECTION_REQUEST_1::class);
    $this->registerPacket(OPEN_CONNECTION_REPLY_1::$ID, OPEN_CONNECTION_REPLY_1::class);
    $this->registerPacket(OPEN_CONNECTION_REQUEST_2::$ID, OPEN_CONNECTION_REQUEST_2::class);
    $this->registerPacket(OPEN_CONNECTION_REPLY_2::$ID, OPEN_CONNECTION_REPLY_2::class);
    $this->registerPacket(UNCONNECTED_PONG::$ID, UNCONNECTED_PONG::class);
    $this->registerPacket(ADVERTISE_SYSTEM::$ID, ADVERTISE_SYSTEM::class);
    $this->registerPacket(DATA_PACKET_0::$ID, DATA_PACKET_0::class);
    $this->registerPacket(DATA_PACKET_1::$ID, DATA_PACKET_1::class);
    $this->registerPacket(DATA_PACKET_2::$ID, DATA_PACKET_2::class);
    $this->registerPacket(DATA_PACKET_3::$ID, DATA_PACKET_3::class);
    $this->registerPacket(DATA_PACKET_4::$ID, DATA_PACKET_4::class);
    $this->registerPacket(DATA_PACKET_5::$ID, DATA_PACKET_5::class);
    $this->registerPacket(DATA_PACKET_6::$ID, DATA_PACKET_6::class);
    $this->registerPacket(DATA_PACKET_7::$ID, DATA_PACKET_7::class);
    $this->registerPacket(DATA_PACKET_8::$ID, DATA_PACKET_8::class);
    $this->registerPacket(DATA_PACKET_9::$ID, DATA_PACKET_9::class);
    $this->registerPacket(DATA_PACKET_A::$ID, DATA_PACKET_A::class);
    $this->registerPacket(DATA_PACKET_B::$ID, DATA_PACKET_B::class);
    $this->registerPacket(DATA_PACKET_C::$ID, DATA_PACKET_C::class);
    $this->registerPacket(DATA_PACKET_D::$ID, DATA_PACKET_D::class);
    $this->registerPacket(DATA_PACKET_E::$ID, DATA_PACKET_E::class);
    $this->registerPacket(DATA_PACKET_F::$ID, DATA_PACKET_F::class);
    $this->registerPacket(NACK::$ID, NACK::class);
    $this->registerPacket(ACK::$ID, ACK::class);
  }
}
