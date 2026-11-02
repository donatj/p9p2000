# PHP Plan 9 9P2000

[![ci.yml](https://github.com/donatj/p9p2000/actions/workflows/ci.yml/badge.svg)](https://github.com/donatj/p9p2000/actions/workflows/ci.yml)


This is a toy implementation of Plan 9's 9P2000 as a PHP stream wrapper, allowing calls such as:

> `file_get_contents('9p://localhost:564/path/to/file');`

It is very buggy and only works with certain 9P2000 servers.

<img width="1004" height="565" alt="image" src="https://github.com/user-attachments/assets/fd13e288-1391-4f67-a171-e49b93e11573" />

## Requirements

- **php**: >=8.1

## Installing

Install the latest version with:

```bash
composer require 'donatj/p9p2000'
```

## Documentation

### Class: donatj\P9p2000\Client

9P2000 Client

---

#### Method: Client::connect

```php
function connect(
	string $host,
	int $port,
	int $msize = \donatj\P9p2000\Protocol::DEFAULT_MSIZE,
): self
```

Connect to a 9P2000 server

**Throws**: `\donatj\P9p2000\Exception\ConnectionException` - If connection to server fails

---

#### Method: Client->version

```php
function version(string $version = '9P2000'): array
```

Perform version negotiation

**Throws**: `\donatj\P9p2000\Exception\ProtocolException` - If version negotiation fails or response is invalid

**Throws**: `\TypeError` - If message type has wrong type

**Throws**: `\ValueError` - If message type value is invalid

##### Return Value

- ***array{msize: int,version: string}***

---

#### Method: Client->attach

```php
function attach(
	string $uname,
	string $aname,
	int $afid = \donatj\P9p2000\Protocol::NOFID,
): int
```

Attach to the file tree

**Throws**: `\donatj\P9p2000\Exception\AttachException` - If attach operation fails

**Throws**: `\donatj\P9p2000\Exception\ProtocolException` - If response is invalid

**Throws**: `\TypeError` - If message type has wrong type

**Throws**: `\ValueError` - If message type value is invalid

---

#### Method: Client->walk

```php
function walk(int $fid, array $path): int
```

Walk to a path

##### Parameters

- ***string[]*** `$path`

**Throws**: `\donatj\P9p2000\Exception\WalkException` - If walk operation fails

**Throws**: `\donatj\P9p2000\Exception\ProtocolException` - If response is invalid

**Throws**: `\TypeError` - If message type has wrong type

**Throws**: `\ValueError` - If message type value is invalid

---

#### Method: Client->open

```php
function open(int $fid, \donatj\P9p2000\OpenMode $mode): array
```

Open a file

**Throws**: `\donatj\P9p2000\Exception\OpenException` - If open operation fails

**Throws**: `\donatj\P9p2000\Exception\ProtocolException` - If response is invalid

**Throws**: `\TypeError` - If message type has wrong type

**Throws**: `\ValueError` - If message type value is invalid

##### Return Value

- ***array{qid: array{type: int,version: int,path: int},iounit: int}***

---

#### Method: Client->create

```php
function create(
	int $fid,
	string $name,
	int $perm,
	\donatj\P9p2000\OpenMode $mode,
): array
```

Create a file or directory

**Throws**: `\donatj\P9p2000\Exception\CreateException` - If create operation fails

**Throws**: `\donatj\P9p2000\Exception\ProtocolException` - If response is invalid

**Throws**: `\TypeError` - If message type has wrong type

**Throws**: `\ValueError` - If message type value is invalid

##### Return Value

- ***array{qid: array{type: int,version: int,path: int},iounit: int}***

---

#### Method: Client->read

```php
function read(int $fid, int $offset, int $count): string
```

Read from a file

**Throws**: `\donatj\P9p2000\Exception\ReadException` - If read operation fails

**Throws**: `\donatj\P9p2000\Exception\ProtocolException` - If response is invalid

**Throws**: `\TypeError` - If message type has wrong type

**Throws**: `\ValueError` - If message type value is invalid

---

#### Method: Client->write

```php
function write(int $fid, int $offset, string $data): int
```

Write to a file

**Throws**: `\donatj\P9p2000\Exception\WriteException` - If write operation fails

**Throws**: `\donatj\P9p2000\Exception\ProtocolException` - If response is invalid

**Throws**: `\TypeError` - If message type has wrong type

**Throws**: `\ValueError` - If message type value is invalid

---

#### Method: Client->stat

```php
function stat(int $fid): array
```

Get file/directory statistics

**Throws**: `\donatj\P9p2000\Exception\StatException` - If stat operation fails

**Throws**: `\donatj\P9p2000\Exception\ProtocolException` - If response is invalid

**Throws**: `\TypeError` - If message type has wrong type

**Throws**: `\ValueError` - If message type value is invalid

##### Return Value

- ***array{type: int,dev: int,qid: array{type: int,version: int,path: int},mode: int,atime: int,mtime: int,length: int,name: string,uid: string,gid: string,muid: string}***

---

#### Method: Client->clunk

```php
function clunk(int $fid): void
```

Close a fid

**Throws**: `\donatj\P9p2000\Exception\ClunkException` - If clunk operation fails

**Throws**: `\donatj\P9p2000\Exception\ProtocolException` - If response is invalid

**Throws**: `\TypeError` - If message type has wrong type

**Throws**: `\ValueError` - If message type value is invalid

---

#### Method: Client->close

```php
function close(): void
```

Close the connection

---

#### Method: Client->__destruct

```php
function __destruct()
```

Undocumented

### Class: donatj\P9p2000\StreamWrapper

Stream Wrapper for 9P2000 protocol
Allows using 9p:// URLs with standard PHP file functions

Usage:
StreamWrapper::register();
  $content = file_get_contents('9p://localhost:564/path/to/file');

#### Method: StreamWrapper::register

```php
function register(string $protocol = '9p'): bool
```

Register the 9p:// protocol handler

---

#### Method: StreamWrapper::unregister

```php
function unregister(string $protocol = '9p'): bool
```

Unregister the protocol handler

---

#### Method: StreamWrapper->stream_open

```php
function stream_open(
	string $path,
	string $mode,
	int $options,
	?string $opened_path,
): bool
```

Open a stream

---

#### Method: StreamWrapper->stream_read

```php
function stream_read(int $count): string
```

Read from stream

---

#### Method: StreamWrapper->stream_write

```php
function stream_write(string $data): int
```

Write to stream

---

#### Method: StreamWrapper->stream_tell

```php
function stream_tell(): int
```

Tell position in stream

---

#### Method: StreamWrapper->stream_eof

```php
function stream_eof(): bool
```

Check if at end of stream

---

#### Method: StreamWrapper->stream_seek

```php
function stream_seek(int $offset, int $whence = SEEK_SET): bool
```

Seek to position

---

#### Method: StreamWrapper->stream_stat

```php
function stream_stat(): array|false
```

Get stream statistics

##### Return Value

- ***array<int|string,int>*** | ***false***

---

#### Method: StreamWrapper->stream_close

```php
function stream_close(): void
```

Close stream

## Full API Docs

[See: DOCS.md](DOCS.md)