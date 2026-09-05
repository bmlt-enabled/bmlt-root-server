<?php

namespace App\Geo;

/**
 * Minimal protobuf wire-format reader, modeled on the parts of the JS `pbf`
 * library that geobuf decoding needs. Operates over an in-memory byte string.
 */
class ProtobufReader
{
    private string $buf;
    private int $pos = 0;
    private int $length;

    public function __construct(string $buf)
    {
        $this->buf = $buf;
        $this->length = strlen($buf);
    }

    public function pos(): int
    {
        return $this->pos;
    }

    public function length(): int
    {
        return $this->length;
    }

    /** Read a field key, returning [fieldNumber, wireType]. */
    public function readTag(): array
    {
        $val = $this->readVarint();
        return [$val >> 3, $val & 0x07];
    }

    /** Read a length-delimited sub-message header; returns its absolute end offset. */
    public function beginMessage(): int
    {
        $len = $this->readVarint();
        return $this->pos + $len;
    }

    public function readVarint(): int
    {
        $result = 0;
        $shift = 0;
        while (true) {
            $byte = ord($this->buf[$this->pos++]);
            $result |= ($byte & 0x7f) << $shift;
            if (($byte & 0x80) === 0) {
                break;
            }
            $shift += 7;
        }
        return $result;
    }

    /** Read a zig-zag encoded signed varint. */
    public function readSVarint(): int
    {
        $n = $this->readVarint();
        return ($n >> 1) ^ -($n & 1);
    }

    public function readString(): string
    {
        $len = $this->readVarint();
        $str = substr($this->buf, $this->pos, $len);
        $this->pos += $len;
        return $str;
    }

    public function readDouble(): float
    {
        $val = unpack('e', substr($this->buf, $this->pos, 8))[1];
        $this->pos += 8;
        return $val;
    }

    public function readBoolean(): bool
    {
        return $this->readVarint() !== 0;
    }

    /** Skip a field's value given its wire type. */
    public function skip(int $wireType): void
    {
        switch ($wireType) {
            case 0:
                $this->readVarint();
                break;
            case 1:
                $this->pos += 8;
                break;
            case 2:
                $len = $this->readVarint();
                $this->pos += $len;
                break;
            case 5:
                $this->pos += 4;
                break;
            default:
                throw new \RuntimeException("Unknown protobuf wire type: {$wireType}");
        }
    }
}
