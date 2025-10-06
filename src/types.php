<?php declare(strict_types=1);

/*
 * Copyright 2024 IBM
 * All Rights Reserved
 * Apache License 2.0 ... see LICENSE file
 * 
 * Original Author: Matthew Wiltzius <matthew.wiltzius@ibm.com>
 */

namespace Mapepire;

/**
 * Job lifecycle states for Mapepire SQL jobs.
 */
enum JobStatus: string
{
  case NotStarted = 'notStarted';
  case Connecting = 'connecting';
  case Ready      = 'ready';
  case Busy       = 'busy';
  case Ended      = 'ended';
}