<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1"><?= e($ticket['ticket_no']) ?></h1>
        <?= status_badge($ticket['status']) ?>
        <span class="badge text-bg-dark"><?= e($ticket['priority']) ?></span>
        <span class="badge text-bg-<?= in_array($ticket['sla_status'], ['Response Overdue','Resolution Overdue','Breached'], true) ? 'danger' : 'success' ?>"><?= e($ticket['sla_status']) ?></span>
    </div>
    <a class="btn btn-outline-secondary btn-sm" href="<?= url('tickets') ?>">Back</a>
</div>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold">Request Details</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><strong>Requester</strong><br><?= e($ticket['requester_name']) ?><br><small><?= e($ticket['requester_position']) ?></small></div>
                    <div class="col-md-6"><strong>Contact</strong><br><?= e($ticket['requester_email']) ?><br><small><?= e($ticket['requester_contact']) ?></small></div>
                    <div class="col-md-6"><strong>Location</strong><br><?= e($ticket['region_name']) ?><br><small><?= e($ticket['office_name']) ?></small></div>
                    <div class="col-md-6"><strong>Service</strong><br><?= e($ticket['category_name']) ?><br><small><?= e($ticket['service_name']) ?></small></div>
                    <div class="col-md-6"><strong>Requested At</strong><br><?= e($ticket['requested_at']) ?></div>
                    <div class="col-md-6"><strong>Requested For</strong><br><?= e($ticket['requested_for']) ?></div>
                    <div class="col-md-6"><strong>Response Due</strong><br><?= e($ticket['response_due_at'] ?? 'Not set') ?></div>
                    <div class="col-md-6"><strong>Resolution Due</strong><br><?= e($ticket['resolution_due_at'] ?? 'Not set') ?></div>
                    <div class="col-md-6"><strong>Responsible Group</strong><br><?= e($ticket['responsible_group'] ?: $ticket['category_name']) ?></div>
                    <div class="col-12"><strong>Description</strong><p class="mb-0 mt-1"><?= nl2br(e($ticket['description'])) ?></p></div>
                </div>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold">Attachments</div>
            <div class="card-body">
                <?php foreach ($attachments as $attachment): ?>
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <div>
                            <a href="<?= url($attachment['file_path']) ?>" target="_blank"><?= e($attachment['original_name']) ?></a>
                            <small class="text-muted d-block"><?= e($attachment['source']) ?> upload by <?= e($attachment['uploaded_by_name'] ?: 'System/User') ?> at <?= e($attachment['created_at']) ?></small>
                        </div>
                        <small class="text-muted"><?= number_format(((int) $attachment['file_size']) / 1024, 1) ?> KB</small>
                    </div>
                <?php endforeach; ?>
                <?php if (!$attachments): ?><p class="text-muted mb-0">No attachments.</p><?php endif; ?>
            </div>
        </div>
        <div class="card">
            <div class="card-header bg-white fw-semibold">Status Timeline</div>
            <div class="card-body">
                <?php foreach ($statusLogs as $log): ?>
                    <div class="timeline-item">
                        <div class="fw-semibold"><?= e($log['new_status']) ?></div>
                        <small class="text-muted"><?= e($log['created_at']) ?> by <?= e($log['changed_by_user_name'] ?: ($log['changed_by_name'] ?: 'System/User')) ?></small>
                        <?php if ($log['remarks']): ?><div><?= e($log['remarks']) ?></div><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold">Assignment</div>
            <div class="card-body">
                <p class="mb-2">Assigned to: <strong><?= e($ticket['assigned_to_name'] ?? 'Unassigned') ?></strong></p>
                <p class="mb-2">Current team: <strong><?= e($ticket['active_assignee_names'] ?: 'Unassigned') ?></strong></p>
                <p class="small text-muted">Assigned at: <?= e($ticket['assigned_at'] ?? 'Not yet assigned') ?></p>
                <?php if ((!$ticket['assigned_to'] || \App\Core\Auth::canManage()) && (\App\Core\Auth::canManage() || (current_user()['role'] ?? '') === 'technical')): ?>
                    <form method="post" action="<?= url('tickets/' . $ticket['id'] . '/assign') ?>" data-confirm="Assign this ticket?">
                        <?= csrf_field() ?>
                        <?php if (\App\Core\Auth::canManage()): ?>
                            <select name="assigned_to" class="form-select mb-2" required>
                                <option value="">Select technical personnel</option>
                                <?php foreach ($technicalUsers as $user): ?>
                                    <option value="<?= (int) $user['id'] ?>"><?= e($user['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="assignment_role" class="form-select mb-2">
                                <option value="<?= $ticket['assigned_to'] ? 'supporting' : 'primary' ?>"><?= $ticket['assigned_to'] ? 'Supporting Assignee' : 'Primary Assignee' ?></option>
                                <?php if (!$ticket['assigned_to']): ?><option value="supporting">Supporting Assignee</option><?php endif; ?>
                            </select>
                        <?php endif; ?>
                        <textarea name="notes" class="form-control mb-2" rows="2" placeholder="Assignment notes"></textarea>
                        <button class="btn btn-primary w-100"><?= \App\Core\Auth::canManage() ? 'Assign Personnel' : 'Assign To Me' ?></button>
                    </form>
                <?php endif; ?>
                <?php if ($assignments): ?>
                    <?php foreach ($assignments as $assignment): ?>
                        <div class="border-top pt-3 mt-3">
                            <div class="fw-semibold"><?= e($assignment['assigned_to_name']) ?></div>
                            <small class="text-muted">Assigned by <?= e($assignment['assigned_by_name']) ?> at <?= e($assignment['assigned_at']) ?></small>
                            <div class="small text-muted"><?= e($assignment['action']) ?> / <?= e($assignment['assignment_role']) ?></div>
                            <?php if ($assignment['previous_assignee_name']): ?><div class="small">Previous: <?= e($assignment['previous_assignee_name']) ?></div><?php endif; ?>
                            <?php if ($assignment['reason']): ?><div class="small mt-1">Reason: <?= e($assignment['reason']) ?></div><?php endif; ?>
                            <?php if ($assignment['notes']): ?><div class="small mt-1"><?= e($assignment['notes']) ?></div><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php $canUpdateStatus = $ticket['assigned_to'] && !in_array($ticket['status'], ['Completed', 'Confirmed Completed', 'Cancelled'], true) && (current_user()['role'] ?? '') === 'technical' && in_array((int)(current_user()['id'] ?? 0), array_map('intval', array_column($currentAssignees, 'user_id')), true); ?>
        <?php if ($canUpdateStatus): ?>
        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold">Update Status</div>
            <div class="card-body">
                <form method="post" action="<?= url('tickets/' . $ticket['id'] . '/status') ?>" enctype="multipart/form-data" data-confirm="Update ticket status?">
                    <?= csrf_field() ?>
                    <select name="status" class="form-select mb-2" required>
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?= e($status) ?>"><?= e($status === 'Cancelled' ? 'Cancel' : $status) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <textarea name="remarks" class="form-control mb-2" rows="2" placeholder="Remarks"></textarea>
                    <input type="file" name="attachments[]" class="form-control mb-2" multiple accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx">
                    <button class="btn btn-success w-100" <?= !$statuses ? 'disabled' : '' ?>>Save Status</button>
                </form>
                <?php if (!$statuses): ?>
                    <p class="text-muted small mb-0 mt-2">All available technical statuses have already been used for this ticket.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php if (\App\Core\Auth::canManage()): ?>
        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold">Reassign Primary</div>
            <div class="card-body">
                <form method="post" action="<?= url('tickets/' . $ticket['id'] . '/reassign') ?>" data-confirm="Reassign this ticket?">
                    <?= csrf_field() ?>
                    <select name="assigned_to" class="form-select mb-2" required>
                        <option value="">Select technical personnel</option>
                        <?php foreach ($technicalUsers as $user): ?>
                            <option value="<?= (int) $user['id'] ?>"><?= e($user['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <textarea name="reason" class="form-control mb-2" rows="2" placeholder="Reassignment reason" required></textarea>
                    <textarea name="notes" class="form-control mb-2" rows="2" placeholder="Remarks"></textarea>
                    <button class="btn btn-outline-primary w-100">Reassign</button>
                </form>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold">Endorse Technical Group</div>
            <div class="card-body">
                <form method="post" action="<?= url('tickets/' . $ticket['id'] . '/endorse') ?>" data-confirm="Endorse this ticket to another group?">
                    <?= csrf_field() ?>
                    <select name="service_category_id" class="form-select mb-2" required>
                        <option value="">Target category</option>
                        <?php foreach ($library->categories() as $category): ?>
                            <option value="<?= (int) $category['id'] ?>"><?= e($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="service_item_id" class="form-select mb-2" required>
                        <option value="">Target service item ID</option>
                        <?php foreach ($library->serviceItems() as $item): ?>
                            <option value="<?= (int) $item['id'] ?>"><?= e($item['category_name']) ?> - <?= e($item['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <textarea name="reason" class="form-control mb-2" rows="2" placeholder="Endorsement reason" required></textarea>
                    <button class="btn btn-outline-secondary w-100">Endorse</button>
                </form>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold">Return / Reopen</div>
            <div class="card-body">
                <form method="post" action="<?= url('tickets/' . $ticket['id'] . '/reopen') ?>" data-confirm="Return this ticket for further action?">
                    <?= csrf_field() ?>
                    <select name="status" class="form-select mb-2">
                        <option value="Returned for Further Action">Returned for Further Action</option>
                        <option value="In Progress">In Progress</option>
                    </select>
                    <textarea name="reason" class="form-control mb-2" rows="2" placeholder="Return/reopen reason" required></textarea>
                    <button class="btn btn-outline-danger w-100">Return Ticket</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<div class="row g-3 mt-1">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold">Feedback</div>
            <div class="card-body">
                <?php foreach ($feedback as $row): ?>
                    <div class="border-bottom py-2">
                        <div class="fw-semibold"><?= e(strtoupper($row['resolved_yes_no'])) ?><?= $row['rating'] ? ' / Rating ' . (int) $row['rating'] : '' ?></div>
                        <small class="text-muted"><?= e($row['created_at']) ?></small>
                        <?php if ($row['feedback_comments']): ?><div class="small mt-1"><?= e($row['feedback_comments']) ?></div><?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php if (!$feedback): ?><p class="text-muted mb-0">No feedback yet.</p><?php endif; ?>
            </div>
        </div>
    </div>
    <?php foreach (['endorsements' => 'Endorsements', 'reopens' => 'Reopen Logs', 'escalations' => 'Escalations'] as $key => $title): ?>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold"><?= e($title) ?></div>
                <div class="card-body">
                    <?php foreach ($histories[$key] as $row): ?>
                        <div class="border-bottom py-2">
                            <small class="text-muted"><?= e($row['created_at']) ?></small>
                            <div class="small"><?= e($row['reason'] ?? ($row['to_group'] ?? $row['new_status'] ?? 'Logged')) ?></div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (!$histories[$key]): ?><p class="text-muted mb-0">No records.</p><?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
