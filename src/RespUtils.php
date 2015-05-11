<?php

namespace Phloppy;

use Phloppy\Exception\CommandException;

/**
 * Redis protocol implementation.
 *
 * This class implements serialization and deserialization of
 * the redis binary protocol. A lot of the code was inspired by
 * ptrofimov/tinyredisclient.
 */
class RespUtils {

    /**
     * @param array $args
     * @return mixed
     */
    public static function serialize(array $args)
    {
        return array_reduce(
            $args,
            function($msg, $arg) { return $msg. '$' .strlen($arg). "\r\n" .$arg. "\r\n"; },
            '*' .count($args). "\r\n"
        );
    }


    /**
     * @param Stream $stream
     * @return array|int|null|string
     *
     * @throws CommandException
     * @throws \RuntimeException
     */
    public static function deserialize(Stream $stream)
    {
        $rsp = $stream->readLine();

        list($type, $result) = [$rsp[0], substr($rsp, 1, strlen($rsp))];

        switch($type) {
         case '-': // ERRORS
             // @TODO take error prefix into account
             throw new CommandException($result);

         case '+': // SIMPLE STRINGS
             return $result;

         case ':': // INTEGERS
             return (int) $result;

         case '$': // BULK STRINGS
             $result = (int) $result;
             if ($result == -1) {
                 return null;
             }

             return trim($stream->readBytes($result + 2));

         case '*': // ARRAYS
             $cnt = (int) $result;
             $out = [];

             for ($i = 0; $i < $cnt; $i++) {
                 $out[] = static::deserialize($stream);
             }

             return $out;

         default:
             throw new \RuntimeException('unhandled protocol response: ' . $rsp);
        }
    }
}
