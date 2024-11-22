<?php

namespace App\Enums;

enum KiloBitrates: int
{
	case Low = 500;
	case Mid = 1500;
	case HD = 4000;
	case FullHD = 6000;
}
