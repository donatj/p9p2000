<?php

namespace donatj\P9p2000;

/**
 * 9P2000 Open Modes
 */
enum OpenMode: int {

	case OREAD = 0x00;
	case OWRITE = 0x01;
	case ORDWR = 0x02;
	case OEXEC = 0x03;
	case OTRUNC = 0x10;
	case ORCLOSE = 0x40;
}
