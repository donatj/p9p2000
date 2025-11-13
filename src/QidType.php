<?php

namespace donatj\P9p2000;

/**
 * 9P2000 Qid Types
 */
enum QidType: int {

	case QTDIR = 0x80;
	case QTAPPEND = 0x40;
	case QTEXCL = 0x20;
	case QTMOUNT = 0x10;
	case QTAUTH = 0x08;
	case QTTMP = 0x04;
	case QTFILE = 0x00;
}
