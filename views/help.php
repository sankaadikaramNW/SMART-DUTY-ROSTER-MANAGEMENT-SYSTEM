<?php
include __DIR__ . '/layout/header.php';
?>
<div class="row mb-4 align-items-center">
    <div class="col-md-12">
        <h2 class="fw-bold mb-1 gradient-text"><i class="fas fa-circle-question"></i> Help & Documentation</h2>
        <p class="text-secondary">Official SLAF Smart Roster Administrative Guideline & Operational Instructions.</p>
    </div>
</div>

<div class="row g-4">
    <!-- WO I/C Role Guide -->
    <div class="col-md-6">
        <div class="glass-card p-4 h-100">
            <h4 class="fw-bold mb-3 text-info"><i class="fas fa-user-shield me-2"></i> Camp Personnel Officer Guide</h4>
            <p class="text-secondary leading-relaxed">
                As the <strong>Camp Personnel Management Officer (Warrant Officer I/C)</strong>, your primary role is the operational administration and lifecycle management of personnel and rosters for your assigned Camp/Base. You do not hold technical IT admin permissions, ensuring a secure segregation of duties.
            </p>
            <ul class="list-unstyled text-secondary mt-3">
                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> <strong>Camp Isolation:</strong> Your dashboard, personnel registries, active roster views, and duty calendars are automatically filtered and restricted strictly to your assigned Camp.</li>
                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> <strong>No Technical Access:</strong> Technical configuration screens such as System Settings, Role Management, and global configurations are restricted.</li>
            </ul>
        </div>
    </div>

    <!-- Personnel & User Account Lifecycle -->
    <div class="col-md-6">
        <div class="glass-card p-4 h-100">
            <h4 class="fw-bold mb-3 text-info"><i class="fas fa-user-tag me-2"></i> Personnel & User Account Lifecycle</h4>
            <p class="text-secondary leading-relaxed">
                Permanent deletion has been removed from the platform. All service members and user accounts are managed through a secure **Archive & Restore** mechanism.
            </p>
            <ul class="list-unstyled text-secondary mt-3">
                <li class="mb-2"><i class="fas fa-arrow-right text-info me-2"></i> <strong>Registering Personnel:</strong> Required fields include Service Number, Name, Rank, Trade, F.1250 ID Card, Section, and Appointment.</li>
                <li class="mb-2"><i class="fas fa-arrow-right text-info me-2"></i> <strong>User Accounts:</strong> You can create active user accounts for your camp's personnel, toggle activation states, and update usernames, but cannot reset passwords, assign high-privilege roles, or restore archived users.</li>
            </ul>
        </div>
    </div>

    <!-- Roster Management -->
    <div class="col-md-6">
        <div class="glass-card p-4 h-100">
            <h4 class="fw-bold mb-3 text-info"><i class="fas fa-calendar-check me-2"></i> Duty Monitoring & Rosters</h4>
            <p class="text-secondary leading-relaxed">
                Supervise active watch schedules, verify guard deployments, and detect schedule overlaps instantly.
            </p>
            <ul class="list-unstyled text-secondary mt-3">
                <li class="mb-2"><i class="fas fa-chevron-right text-warning me-2"></i> <strong>View-Only Watch Calendar:</strong> Roster duty shifts are visual and interactive but fully read-only to prevent unauthorized alterations.</li>
                <li class="mb-2"><i class="fas fa-chevron-right text-warning me-2"></i> <strong>Return for Correction:</strong> While you do not authorize roster approval (restricted to OCPROVST), you can review submitted drafts and select **"Return to Draft"** with structural correction notes.</li>
            </ul>
        </div>
    </div>

    <!-- Postings & Transfers -->
    <div class="col-md-6">
        <div class="glass-card p-4 h-100">
            <h4 class="fw-bold mb-3 text-info"><i class="fas fa-right-left me-2"></i> Postings & Transfers</h4>
            <p class="text-secondary leading-relaxed">
                Track personnel deployments to/from other bases.
            </p>
            <ul class="list-unstyled text-secondary mt-3">
                <li class="mb-2"><i class="fas fa-info-circle text-primary me-2"></i> <strong>Inbound & Outbound:</strong> View active postings, transfer histories, and pending authorizations for camp staff.</li>
                <li class="mb-2"><i class="fas fa-info-circle text-primary me-2"></i> <strong>Approvals:</strong> Endorsements of postings and transfers are managed by OCPROVST and Administrators.</li>
            </ul>
        </div>
    </div>
</div>

<div class="glass-card mt-4 p-4 text-center">
    <h5 class="fw-bold text-dark mb-2"><i class="fas fa-shield-halved text-warning me-2"></i> Audit Logging Notice</h5>
    <p class="text-secondary mb-0 small">
        To maintain military operational integrity, every transaction, including view profiles, roster checks, reports, and logins, is permanently written to the immutable audit trail.
    </p>
</div>

<?php
include __DIR__ . '/layout/footer.php';
?>
