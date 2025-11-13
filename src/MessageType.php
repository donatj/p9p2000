<?php

namespace donatj\P9p2000;

/**
 * 9P2000 Message Types
 */
enum MessageType: int {

	case Tversion = 100;
	case Rversion = 101;
	case Tauth = 102;
	case Rauth = 103;
	case Tattach = 104;
	case Rattach = 105;
	case Terror = 106; // illegal
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
