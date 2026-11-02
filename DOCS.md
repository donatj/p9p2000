# Full API Docs

## Class: donatj\P9p2000\Client

9P2000 Client

---

### Method: Client::connect

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

### Method: Client->version

```php
function version(string $version = '9P2000'): array
```

Perform version negotiation

**Throws**: `\donatj\P9p2000\Exception\ProtocolException` - If version negotiation fails or response is invalid

**Throws**: `\TypeError` - If message type has wrong type

**Throws**: `\ValueError` - If message type value is invalid

#### Return Value

- ***array{msize: int,version: string}***

---

### Method: Client->attach

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

### Method: Client->walk

```php
function walk(int $fid, array $path): int
```

Walk to a path

#### Parameters

- ***string[]*** `$path`

**Throws**: `\donatj\P9p2000\Exception\WalkException` - If walk operation fails

**Throws**: `\donatj\P9p2000\Exception\ProtocolException` - If response is invalid

**Throws**: `\TypeError` - If message type has wrong type

**Throws**: `\ValueError` - If message type value is invalid

---

### Method: Client->open

```php
function open(int $fid, \donatj\P9p2000\OpenMode $mode): array
```

Open a file

**Throws**: `\donatj\P9p2000\Exception\OpenException` - If open operation fails

**Throws**: `\donatj\P9p2000\Exception\ProtocolException` - If response is invalid

**Throws**: `\TypeError` - If message type has wrong type

**Throws**: `\ValueError` - If message type value is invalid

#### Return Value

- ***array{qid: array{type: int,version: int,path: int},iounit: int}***

---

### Method: Client->create

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

#### Return Value

- ***array{qid: array{type: int,version: int,path: int},iounit: int}***

---

### Method: Client->read

```php
function read(int $fid, int $offset, int $count): string
```

Read from a file

**Throws**: `\donatj\P9p2000\Exception\ReadException` - If read operation fails

**Throws**: `\donatj\P9p2000\Exception\ProtocolException` - If response is invalid

**Throws**: `\TypeError` - If message type has wrong type

**Throws**: `\ValueError` - If message type value is invalid

---

### Method: Client->write

```php
function write(int $fid, int $offset, string $data): int
```

Write to a file

**Throws**: `\donatj\P9p2000\Exception\WriteException` - If write operation fails

**Throws**: `\donatj\P9p2000\Exception\ProtocolException` - If response is invalid

**Throws**: `\TypeError` - If message type has wrong type

**Throws**: `\ValueError` - If message type value is invalid

---

### Method: Client->stat

```php
function stat(int $fid): array
```

Get file/directory statistics

**Throws**: `\donatj\P9p2000\Exception\StatException` - If stat operation fails

**Throws**: `\donatj\P9p2000\Exception\ProtocolException` - If response is invalid

**Throws**: `\TypeError` - If message type has wrong type

**Throws**: `\ValueError` - If message type value is invalid

#### Return Value

- ***array{type: int,dev: int,qid: array{type: int,version: int,path: int},mode: int,atime: int,mtime: int,length: int,name: string,uid: string,gid: string,muid: string}***

---

### Method: Client->clunk

```php
function clunk(int $fid): void
```

Close a fid

**Throws**: `\donatj\P9p2000\Exception\ClunkException` - If clunk operation fails

**Throws**: `\donatj\P9p2000\Exception\ProtocolException` - If response is invalid

**Throws**: `\TypeError` - If message type has wrong type

**Throws**: `\ValueError` - If message type value is invalid

---

### Method: Client->close

```php
function close(): void
```

Close the connection

---

### Method: Client->__destruct

```php
function __destruct()
```

## Class: donatj\P9p2000\Exception\AttachException

Exception thrown when Tattach operation fails

### Method: AttachException->__construct

```php
function __construct(
	string $errorMessage,
	string $username,
	string $aname,
	?\Throwable $previous = null,
)
```

---

### Method: AttachException->getUsername

```php
function getUsername(): string
```

---

### Method: AttachException->getAname

```php
function getAname(): string
```

---

### Method: AttachException->getOperation

```php
function getOperation(): string
```

---

### Method: AttachException->getErrorMessage

```php
function getErrorMessage(): string
```

## Class: donatj\P9p2000\Exception\ClunkException

Exception thrown when Tclunk operation fails

### Method: ClunkException->__construct

```php
function __construct(
	string $errorMessage,
	int $fid,
	?\Throwable $previous = null,
)
```

---

### Method: ClunkException->getFid

```php
function getFid(): int
```

---

### Method: ClunkException->getOperation

```php
function getOperation(): string
```

---

### Method: ClunkException->getErrorMessage

```php
function getErrorMessage(): string
```

## Class: donatj\P9p2000\Exception\ConnectionException

Exception thrown when connection to 9P2000 server fails

### Method: ConnectionException->__construct

```php
function __construct(
	string $message,
	string $host,
	int $port,
	int $errorCode,
	?\Throwable $previous = null,
)
```

---

### Method: ConnectionException->getHost

```php
function getHost(): string
```

---

### Method: ConnectionException->getPort

```php
function getPort(): int
```

---

### Method: ConnectionException->getErrorCode

```php
function getErrorCode(): int
```

## Class: donatj\P9p2000\Exception\CreateException

Exception thrown when Tcreate operation fails

### Method: CreateException->__construct

```php
function __construct(
	string $errorMessage,
	string $name,
	int $permissions,
	\donatj\P9p2000\OpenMode $mode,
	?\Throwable $previous = null,
)
```

---

### Method: CreateException->getName

```php
function getName(): string
```

---

### Method: CreateException->getPermissions

```php
function getPermissions(): int
```

---

### Method: CreateException->getMode

```php
function getMode(): \donatj\P9p2000\OpenMode
```

---

### Method: CreateException->getOperation

```php
function getOperation(): string
```

---

### Method: CreateException->getErrorMessage

```php
function getErrorMessage(): string
```

## Class: donatj\P9p2000\Exception\OpenException

Exception thrown when Topen operation fails

### Method: OpenException->__construct

```php
function __construct(
	string $errorMessage,
	int $fid,
	\donatj\P9p2000\OpenMode $mode,
	?\Throwable $previous = null,
)
```

---

### Method: OpenException->getFid

```php
function getFid(): int
```

---

### Method: OpenException->getMode

```php
function getMode(): \donatj\P9p2000\OpenMode
```

---

### Method: OpenException->getOperation

```php
function getOperation(): string
```

---

### Method: OpenException->getErrorMessage

```php
function getErrorMessage(): string
```

## Class: donatj\P9p2000\Exception\P9p2000Exception

Base exception for all 9P2000 client errors

## Class: donatj\P9p2000\Exception\ProtocolException

Exception thrown when protocol-level errors occur

## Class: donatj\P9p2000\Exception\ReadException

Exception thrown when Tread operation fails

### Method: ReadException->__construct

```php
function __construct(
	string $errorMessage,
	int $fid,
	int $offset,
	int $count,
	?\Throwable $previous = null,
)
```

---

### Method: ReadException->getFid

```php
function getFid(): int
```

---

### Method: ReadException->getOffset

```php
function getOffset(): int
```

---

### Method: ReadException->getCount

```php
function getCount(): int
```

---

### Method: ReadException->getOperation

```php
function getOperation(): string
```

---

### Method: ReadException->getErrorMessage

```php
function getErrorMessage(): string
```

## Class: donatj\P9p2000\Exception\ServerErrorException

Exception thrown when the 9P2000 server returns an Rerror response

### Method: ServerErrorException->__construct

```php
function __construct(
	string $operation,
	string $errorMessage,
	?\Throwable $previous = null,
)
```

---

### Method: ServerErrorException->getOperation

```php
function getOperation(): string
```

---

### Method: ServerErrorException->getErrorMessage

```php
function getErrorMessage(): string
```

## Class: donatj\P9p2000\Exception\StatException

Exception thrown when Tstat operation fails

### Method: StatException->__construct

```php
function __construct(
	string $errorMessage,
	int $fid,
	?\Throwable $previous = null,
)
```

---

### Method: StatException->getFid

```php
function getFid(): int
```

---

### Method: StatException->getOperation

```php
function getOperation(): string
```

---

### Method: StatException->getErrorMessage

```php
function getErrorMessage(): string
```

## Class: donatj\P9p2000\Exception\WalkException

Exception thrown when Twalk operation fails

### Method: WalkException->__construct

```php
function __construct(
	string $errorMessage,
	array $path,
	?\Throwable $previous = null,
)
```

#### Parameters

- ***string[]*** `$path`

---

### Method: WalkException->getPath

```php
function getPath(): array
```

#### Return Value

- ***string[]***

---

### Method: WalkException->getOperation

```php
function getOperation(): string
```

---

### Method: WalkException->getErrorMessage

```php
function getErrorMessage(): string
```

## Class: donatj\P9p2000\Exception\WriteException

Exception thrown when Twrite operation fails

### Method: WriteException->__construct

```php
function __construct(
	string $errorMessage,
	int $fid,
	int $offset,
	int $dataLength,
	?\Throwable $previous = null,
)
```

---

### Method: WriteException->getFid

```php
function getFid(): int
```

---

### Method: WriteException->getOffset

```php
function getOffset(): int
```

---

### Method: WriteException->getDataLength

```php
function getDataLength(): int
```

---

### Method: WriteException->getOperation

```php
function getOperation(): string
```

---

### Method: WriteException->getErrorMessage

```php
function getErrorMessage(): string
```

## Class: donatj\P9p2000\Message

Helper class for encoding/decoding 9P2000 messages

---

### Method: Message::encodeString

```php
function encodeString(string $str): string
```

Encode a string with its length prefix (2 bytes)

---

### Method: Message::decodeString

```php
function decodeString(string $data, int $offset): array
```

Decode a string with length prefix  
Returns: [string, newOffset]

**Throws**: `\donatj\P9p2000\Exception\ProtocolException` - If string decoding fails

#### Return Value

- ***array{0: string,1: int}***

---

### Method: Message::encodeQid

```php
function encodeQid(int $type, int $version, int $path): string
```

Encode a Qid (13 bytes: type[1] version[4] path[8])

---

### Method: Message::decodeQid

```php
function decodeQid(string $data, int $offset): array
```

Decode a Qid  
Returns: [qid, newOffset]

**Throws**: `\donatj\P9p2000\Exception\ProtocolException` - If Qid decoding fails

#### Return Value

- ***array{0: array{type: int,version: int,path: int},1: int}***

---

### Method: Message::encodeHeader

```php
function encodeHeader(
	\donatj\P9p2000\MessageType $type,
	int $tag,
	string $body,
): string
```

Encode a message header (size[4] type[1] tag[2])

---

### Method: Message::decodeHeader

```php
function decodeHeader(string $data): array
```

Decode message header

**Throws**: `\donatj\P9p2000\Exception\ProtocolException` - If header decoding fails

**Throws**: `\TypeError` - If message type has wrong type

#### Return Value

- ***array{size: int,type: \donatj\P9p2000\MessageType,tag: int,body: string}***

## Enum: donatj\P9p2000\MessageType

9P2000 Message Types

```php
<?php
namespace donatj\P9p2000;

enum MessageType: int {
	case Tversion = 100;
	case Rversion = 101;
	case Tauth = 102;
	case Rauth = 103;
	case Tattach = 104;
	case Rattach = 105;
	case Terror = 106;
	case Rerror = 107;
	case Tflush = 108;
	case Rflush = 109;
	case Twalk = 110;
	case Rwalk = 111;
	case Topen = 112;
	case Ropen = 113;
	case Tcreate = 114;
	case Rcreate = 115;
	case Tread = 116;
	case Rread = 117;
	case Twrite = 118;
	case Rwrite = 119;
	case Tclunk = 120;
	case Rclunk = 121;
	case Tremove = 122;
	case Rremove = 123;
	case Tstat = 124;
	case Rstat = 125;
	case Twstat = 126;
	case Rwstat = 127;
}
```

## Enum: donatj\P9p2000\OpenMode

9P2000 Open Modes

```php
<?php
namespace donatj\P9p2000;

enum OpenMode: int {
	case OREAD = 0x0;
	case OWRITE = 0x1;
	case ORDWR = 0x2;
	case OEXEC = 0x3;
	case OTRUNC = 0x10;
	case ORCLOSE = 0x40;
}
```

## Class: donatj\P9p2000\Protocol

9P2000 Protocol Constants

```php
<?php
namespace donatj\P9p2000;

class Protocol {
	public const DEFAULT_MSIZE = 8192;
	public const NOTAG = 0xffff;
	public const NOFID = 0xffffffff;
}
```

## Enum: donatj\P9p2000\QidType

9P2000 Qid Types

```php
<?php
namespace donatj\P9p2000;

enum QidType: int {
	case QTDIR = 0x80;
	case QTAPPEND = 0x40;
	case QTEXCL = 0x20;
	case QTMOUNT = 0x10;
	case QTAUTH = 0x8;
	case QTTMP = 0x4;
	case QTFILE = 0x0;
}
```

## Class: donatj\P9p2000\StreamWrapper

Stream Wrapper for 9P2000 protocol
Allows using 9p:// URLs with standard PHP file functions

Usage:
StreamWrapper::register();
  $content = file_get_contents('9p://localhost:564/path/to/file');

### Method: StreamWrapper::register

```php
function register(string $protocol = '9p'): bool
```

Register the 9p:// protocol handler

---

### Method: StreamWrapper::unregister

```php
function unregister(string $protocol = '9p'): bool
```

Unregister the protocol handler

---

### Method: StreamWrapper->stream_open

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

### Method: StreamWrapper->stream_read

```php
function stream_read(int $count): string
```

Read from stream

**Throws**: `\donatj\P9p2000\Exception\ProtocolException` - If the server sends an invalid response

---

### Method: StreamWrapper->stream_write

```php
function stream_write(string $data): int
```

Write to stream

---

### Method: StreamWrapper->stream_tell

```php
function stream_tell(): int
```

Tell position in stream

---

### Method: StreamWrapper->stream_eof

```php
function stream_eof(): bool
```

Check if at end of stream

---

### Method: StreamWrapper->stream_seek

```php
function stream_seek(int $offset, int $whence = SEEK_SET): bool
```

Seek to position

---

### Method: StreamWrapper->stream_stat

```php
function stream_stat(): array|false
```

Get stream statistics

#### Return Value

- ***array<int|string,int>*** | ***false***

---

### Method: StreamWrapper->stream_close

```php
function stream_close(): void
```

Close stream