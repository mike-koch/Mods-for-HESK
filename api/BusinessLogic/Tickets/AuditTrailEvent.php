<?php

namespace BusinessLogic\Tickets;


class AuditTrailEvent extends \BaseClass {
    const DUE_DATE_REMOVED = 'audit_due_date_removed';
    const DUE_DATE_CHANGED = 'audit_due_date_changed';
}