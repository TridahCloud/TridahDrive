@extends('layouts.homepage')

@section('title', 'Project Board - Team Collaboration & Task Management')

@section('content')
<!-- Hero Section -->
<section class="hero-section landing-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="hero-title">Project Board - Team Collaboration Made Simple</h1>
                    <p class="hero-subtitle">
                        Organize projects, manage tasks, and collaborate with your team. Kanban boards, task tracking, and team communication all in one place.
                    </p>
                    <div class="hero-buttons">
                        <a href="{{ route('register') }}" class="btn btn-lg btn-primary">
                            <i class="fas fa-rocket me-2"></i>
                            Get Started Free
                        </a>
                        <a href="#features" class="btn btn-lg btn-outline-light btn-outline-teal">
                            <i class="fas fa-eye me-2"></i>
                            See How It Works
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-preview">
                    <div class="browser-window hero-browser" data-app="project-board">
                        <div class="browser-header">
                            <div class="browser-controls">
                                <span class="browser-dot"></span>
                                <span class="browser-dot"></span>
                                <span class="browser-dot"></span>
                            </div>
                            <div class="browser-url">drive.tridah.cloud/projects</div>
                        </div>
                        <div class="browser-content project-preview-large">
                            <div class="kanban-preview-large">
                                <div class="kanban-board-large">
                                    <div class="kanban-column-large">
                                        <div class="kanban-header-large">To Do</div>
                                        <div class="kanban-task-large">
                                            <div class="task-label-large bg-info"></div>
                                            <div class="task-content">
                                                <strong>Design Mockups</strong>
                                                <small>High Priority</small>
                                            </div>
                                        </div>
                                        <div class="kanban-task-large">
                                            <div class="task-label-large bg-warning"></div>
                                            <div class="task-content">
                                                <strong>Update Documentation</strong>
                                                <small>Medium Priority</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="kanban-column-large">
                                        <div class="kanban-header-large">In Progress</div>
                                        <div class="kanban-task-large">
                                            <div class="task-label-large bg-success"></div>
                                            <div class="task-content">
                                                <strong>API Integration</strong>
                                                <small>Assigned to John</small>
                                            </div>
                                        </div>
                                        <div class="kanban-task-large">
                                            <div class="task-label-large bg-primary"></div>
                                            <div class="task-content">
                                                <strong>Feature Development</strong>
                                                <small>In Review</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="kanban-column-large">
                                        <div class="kanban-header-large">Done</div>
                                        <div class="kanban-task-large">
                                            <div class="task-label-large bg-secondary"></div>
                                            <div class="task-content">
                                                <strong>User Testing</strong>
                                                <small>Completed</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="section features-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Powerful Project Management Features</h2>
            <p class="section-subtitle">Everything you need to manage projects and collaborate with your team</p>
        </div>
        
        <div class="row g-5 mb-5">
            <!-- Feature 1: Kanban Boards -->
            <div class="col-lg-6">
                <div class="feature-content">
                    <div class="feature-icon-large">
                        <i class="fas fa-columns"></i>
                    </div>
                    <h3 class="feature-heading">Kanban Boards</h3>
                    <p class="feature-text">
                        Visualize your workflow with intuitive kanban boards. Drag and drop tasks between columns, track progress, and stay organized.
                    </p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check text-success"></i> Drag and drop task management</li>
                        <li><i class="fas fa-check text-success"></i> Customizable columns</li>
                        <li><i class="fas fa-check text-success"></i> Visual progress tracking</li>
                        <li><i class="fas fa-check text-success"></i> Real-time updates</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="app-preview-container feature-preview">
                    <div class="browser-window" data-app="project-kanban">
                        <div class="browser-header">
                            <div class="browser-controls">
                                <span class="browser-dot"></span>
                                <span class="browser-dot"></span>
                                <span class="browser-dot"></span>
                            </div>
                            <div class="browser-url">drive.tridah.cloud/projects/view/kanban</div>
                        </div>
                        <div class="browser-content project-preview">
                            <div class="kanban-board">
                                <div class="kanban-column">
                                    <div class="kanban-header">To Do</div>
                                    <div class="kanban-task">
                                        <div class="task-label bg-info"></div>
                                        <strong>Design Mockups</strong>
                                        <small>High Priority</small>
                                    </div>
                                    <div class="kanban-task">
                                        <div class="task-label bg-warning"></div>
                                        <strong>Update Docs</strong>
                                        <small>Medium Priority</small>
                                    </div>
                                </div>
                                <div class="kanban-column">
                                    <div class="kanban-header">In Progress</div>
                                    <div class="kanban-task">
                                        <div class="task-label bg-success"></div>
                                        <strong>API Integration</strong>
                                        <small>Assigned to John</small>
                                    </div>
                                </div>
                                <div class="kanban-column">
                                    <div class="kanban-header">Done</div>
                                    <div class="kanban-task">
                                        <div class="task-label bg-secondary"></div>
                                        <strong>User Testing</strong>
                                        <small>Completed</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-5 mb-5">
            <!-- Feature 2: Task Management -->
            <div class="col-lg-6 order-lg-2">
                <div class="feature-content">
                    <div class="feature-icon-large">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <h3 class="feature-heading">Task Management</h3>
                    <p class="feature-text">
                        Create, assign, and track tasks with labels, priorities, due dates, and comments. Keep your team aligned and productive.
                    </p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check text-success"></i> Task labels and priorities</li>
                        <li><i class="fas fa-check text-success"></i> Assign tasks to team members</li>
                        <li><i class="fas fa-check text-success"></i> Due dates and reminders</li>
                        <li><i class="fas fa-check text-success"></i> Task comments and attachments</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6 order-lg-1">
                <div class="app-preview-container feature-preview">
                    <div class="browser-window" data-app="project-tasks">
                        <div class="browser-header">
                            <div class="browser-controls">
                                <span class="browser-dot"></span>
                                <span class="browser-dot"></span>
                                <span class="browser-dot"></span>
                            </div>
                            <div class="browser-url">drive.tridah.cloud/projects/tasks</div>
                        </div>
                        <div class="browser-content project-preview">
                            <div class="task-detail-preview">
                                <div class="task-header">
                                    <h5>API Integration</h5>
                                    <span class="badge bg-success">In Progress</span>
                                </div>
                                <div class="task-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-user"></i>
                                        <span>Assigned to: John Doe</span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-calendar"></i>
                                        <span>Due: Jan 20, 2025</span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-tag"></i>
                                        <span class="badge bg-primary">Development</span>
                                    </div>
                                </div>
                                <div class="task-description">
                                    <p>Integrate third-party API for payment processing. Ensure error handling and proper authentication.</p>
                                </div>
                                <div class="task-comments">
                                    <h6>Comments (2)</h6>
                                    <div class="comment-item">
                                        <strong>John Doe</strong>
                                        <p>Starting implementation now</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-5">
            <!-- Feature 3: Team Collaboration -->
            <div class="col-lg-6">
                <div class="feature-content">
                    <div class="feature-icon-large">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="feature-heading">Team Collaboration</h3>
                    <p class="feature-text">
                        Work together seamlessly with your team. Share projects, collaborate on tasks, and keep everyone in sync.
                    </p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check text-success"></i> Share projects with team members</li>
                        <li><i class="fas fa-check text-success"></i> Real-time collaboration</li>
                        <li><i class="fas fa-check text-success"></i> Task comments and discussions</li>
                        <li><i class="fas fa-check text-success"></i> Activity tracking and notifications</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="app-preview-container feature-preview">
                    <div class="browser-window" data-app="project-team">
                        <div class="browser-header">
                            <div class="browser-controls">
                                <span class="browser-dot"></span>
                                <span class="browser-dot"></span>
                                <span class="browser-dot"></span>
                            </div>
                            <div class="browser-url">drive.tridah.cloud/projects/members</div>
                        </div>
                        <div class="browser-content project-preview">
                            <div class="team-preview">
                                <div class="team-header">
                                    <h5>Project Team</h5>
                                    <button class="preview-btn btn-sm"><i class="fas fa-user-plus"></i> Add Member</button>
                                </div>
                                <div class="team-members">
                                    <div class="member-item">
                                        <div class="member-avatar bg-primary">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="member-info">
                                            <strong>John Doe</strong>
                                            <small>Developer</small>
                                        </div>
                                        <span class="badge bg-success">5 Tasks</span>
                                    </div>
                                    <div class="member-item">
                                        <div class="member-avatar bg-info">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="member-info">
                                            <strong>Jane Smith</strong>
                                            <small>Designer</small>
                                        </div>
                                        <span class="badge bg-success">3 Tasks</span>
                                    </div>
                                    <div class="member-item">
                                        <div class="member-avatar bg-warning">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="member-info">
                                            <strong>Mike Johnson</strong>
                                            <small>Manager</small>
                                        </div>
                                        <span class="badge bg-info">Reviewing</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Benefits Section -->
<section class="section benefits-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4 mb-lg-0">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <h4>Stay Organized</h4>
                    <p>Keep all your projects and tasks organized in one place. Never miss a deadline or lose track of progress.</p>
                </div>
            </div>
            <div class="col-lg-4 mb-4 mb-lg-0">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h4>Team Collaboration</h4>
                    <p>Work together seamlessly with your team. Share projects, assign tasks, and communicate in real-time.</p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4>Track Progress</h4>
                    <p>Visualize your workflow with kanban boards and track project progress with detailed analytics.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="section cta-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <div class="cta-card">
                    <div class="cta-icon mb-4">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <h2 class="fw-bold mb-3">Ready to Organize Your Projects?</h2>
                    <p class="cta-text mb-4">
                        Start managing your projects with ease. Collaborate with your team, track progress, and get things done faster.
                    </p>
                    <div class="d-flex flex-wrap gap-3 justify-content-center">
                        <a href="{{ route('register') }}" class="btn btn-lg btn-primary">
                            <i class="fas fa-rocket me-2"></i>
                            Start Free Today
                        </a>
                        <a href="{{ route('home') }}" class="btn btn-lg btn-outline-teal">
                            <i class="fas fa-arrow-left me-2"></i>
                            Learn More
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
/* Base Browser Window Styles */
.app-preview-container {
    position: relative;
    perspective: 1200px;
    transform-style: preserve-3d;
}

.browser-window {
    background: #1e1e2e;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4),
                0 0 0 1px rgba(49, 216, 178, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.05);
    transform: translateZ(0) rotateX(2deg);
    transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
    position: relative;
    z-index: 1;
}

.app-preview-container:hover .browser-window {
    transform: translateY(-15px) translateZ(20px) rotateX(-5deg) rotateY(2deg);
    box-shadow: 0 30px 80px rgba(49, 216, 178, 0.2),
                0 0 0 1px rgba(49, 216, 178, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
}

.browser-header {
    background: linear-gradient(180deg, #2a2a3a 0%, #1e1e2e 100%);
    padding: 10px 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    border-bottom: 1px solid rgba(49, 216, 178, 0.1);
}

.browser-controls {
    display: flex;
    gap: 6px;
}

.browser-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: inline-block;
}

.browser-dot:nth-child(1) { background: #ff5f56; }
.browser-dot:nth-child(2) { background: #ffbd2e; }
.browser-dot:nth-child(3) { background: #27c93f; }

.browser-url {
    flex: 1;
    background: rgba(0, 0, 0, 0.3);
    border-radius: 4px;
    padding: 4px 10px;
    font-size: 0.7rem;
    color: rgba(255, 255, 255, 0.6);
    font-family: 'Courier New', monospace;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.browser-content {
    padding: 12px;
    background: #1a1a24;
    min-height: 280px;
    position: relative;
    overflow: hidden;
}

/* Preview Styles */
.project-preview {
    background: #1a1a24;
}

.kanban-board {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
}

.kanban-column {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(49, 216, 178, 0.1);
    border-radius: 8px;
    padding: 8px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-height: 200px;
}

.kanban-header {
    font-size: 0.7rem;
    font-weight: bold;
    color: rgba(255, 255, 255, 0.7);
    padding: 4px 0;
    border-bottom: 1px solid rgba(49, 216, 178, 0.1);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.kanban-task {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(49, 216, 178, 0.1);
    border-radius: 6px;
    padding: 8px;
    font-size: 0.7rem;
    transition: all 0.3s;
    transform: translateZ(0);
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.kanban-task:hover {
    background: rgba(49, 216, 178, 0.1);
    border-color: rgba(49, 216, 178, 0.3);
    transform: translateY(-2px) translateZ(5px);
    box-shadow: 0 4px 12px rgba(49, 216, 178, 0.2);
}

.task-label {
    position: absolute;
    top: 6px;
    right: 6px;
    width: 4px;
    height: 20px;
    border-radius: 2px;
}

.kanban-task strong {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.75rem;
}

.kanban-task small {
    color: rgba(255, 255, 255, 0.5);
    font-size: 0.65rem;
}

.preview-btn {
    background: linear-gradient(135deg, #31d8b2 0%, #204e7e 100%);
    border: none;
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.75rem;
    cursor: pointer;
    transition: transform 0.2s;
}

.preview-btn:hover {
    transform: scale(1.05);
}

.landing-hero {
    min-height: 80vh;
    display: flex;
    align-items: center;
    padding: 4rem 0;
}

.hero-preview {
    position: relative;
    perspective: 1500px;
}

.hero-browser {
    transform: translateZ(0) rotateX(2deg) rotateY(-2deg);
    box-shadow: 0 30px 100px rgba(49, 216, 178, 0.2);
}

.project-preview-large {
    min-height: 400px;
    padding: 20px;
}

.kanban-preview-large {
    background: rgba(255, 255, 255, 0.03);
    border-radius: 8px;
    padding: 20px;
}

.kanban-board-large {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
}

.kanban-column-large {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(49, 216, 178, 0.1);
    border-radius: 8px;
    padding: 15px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    min-height: 300px;
}

.kanban-header-large {
    font-size: 0.85rem;
    font-weight: bold;
    color: rgba(255, 255, 255, 0.7);
    padding: 8px 0;
    border-bottom: 1px solid rgba(49, 216, 178, 0.2);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.kanban-task-large {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(49, 216, 178, 0.1);
    border-radius: 6px;
    padding: 12px;
    position: relative;
    display: flex;
    gap: 10px;
}

.task-label-large {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 4px;
    height: 24px;
    border-radius: 2px;
}

.task-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.task-content strong {
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.9);
}

.task-content small {
    font-size: 0.7rem;
    color: rgba(255, 255, 255, 0.6);
}

.task-detail-preview {
    padding: 15px;
}

.task-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(49, 216, 178, 0.2);
}

.task-header h5 {
    margin: 0;
    font-size: 1.1rem;
    color: rgba(255, 255, 255, 0.9);
}

.task-meta {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 15px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.7);
}

.task-description {
    margin: 15px 0;
    padding: 12px;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 6px;
}

.task-description p {
    margin: 0;
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.8);
    line-height: 1.6;
}

.task-comments {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid rgba(49, 216, 178, 0.2);
}

.task-comments h6 {
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.comment-item {
    padding: 10px;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 6px;
    margin-bottom: 8px;
}

.comment-item strong {
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.9);
    display: block;
    margin-bottom: 4px;
}

.comment-item p {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.7);
    margin: 0;
}

.team-preview {
    padding: 15px;
}

.team-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid rgba(49, 216, 178, 0.2);
}

.team-header h5 {
    margin: 0;
    font-size: 0.95rem;
    color: rgba(255, 255, 255, 0.9);
}

.team-members {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.member-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(49, 216, 178, 0.1);
    border-radius: 8px;
}

.member-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.member-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.member-info strong {
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.9);
}

.member-info small {
    font-size: 0.7rem;
    color: rgba(255, 255, 255, 0.6);
}

.features-section {
    background: linear-gradient(135deg, #1a1a24 0%, #141420 100%);
    padding: 5rem 0;
}

.feature-content {
    padding: 2rem 0;
}

.feature-icon-large {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #31d8b2 0%, #204e7e 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    margin-bottom: 2rem;
}

.feature-heading {
    font-size: 2rem;
    font-weight: bold;
    color: #31d8b2;
    margin-bottom: 1rem;
}

.feature-text {
    color: rgba(255, 255, 255, 0.8);
    font-size: 1.1rem;
    line-height: 1.8;
    margin-bottom: 1.5rem;
}

.feature-list {
    list-style: none;
    padding: 0;
}

.feature-list li {
    padding: 0.75rem 0;
    color: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.feature-preview {
    max-width: 100%;
}

.benefits-section {
    padding: 5rem 0;
    background: #1e1e2e;
}

.benefit-card {
    text-align: center;
    padding: 2rem;
    background: rgba(255, 255, 255, 0.02);
    border-radius: 12px;
    border: 1px solid rgba(49, 216, 178, 0.1);
    transition: all 0.3s;
    height: 100%;
}

.benefit-card:hover {
    transform: translateY(-10px);
    border-color: rgba(49, 216, 178, 0.3);
    box-shadow: 0 20px 60px rgba(49, 216, 178, 0.1);
}

.benefit-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #31d8b2 0%, #204e7e 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    margin: 0 auto 1.5rem;
}

.benefit-card h4 {
    color: #31d8b2;
    font-weight: bold;
    margin-bottom: 1rem;
}

.benefit-card p {
    color: rgba(255, 255, 255, 0.8);
    line-height: 1.8;
}

.cta-section {
    padding: 5rem 0;
    background: linear-gradient(135deg, #141420 0%, #1a1a24 100%);
}

[data-theme="light"] .feature-text,
[data-theme="light"] .feature-list li {
    color: rgba(30, 30, 40, 0.9);
}

[data-theme="light"] .benefit-card {
    background: rgba(32, 78, 126, 0.02);
    border-color: rgba(32, 78, 126, 0.1);
}

[data-theme="light"] .benefit-card p {
    color: rgba(30, 30, 40, 0.8);
}

[data-theme="light"] .kanban-preview-large,
[data-theme="light"] .task-detail-preview,
[data-theme="light"] .team-preview {
    background: rgba(32, 78, 126, 0.02);
}

[data-theme="light"] .task-header h5,
[data-theme="light"] .task-content strong,
[data-theme="light"] .member-info strong {
    color: rgba(30, 30, 40, 0.9);
}

[data-theme="light"] .task-description p,
[data-theme="light"] .comment-item p {
    color: rgba(30, 30, 40, 0.8);
}

/* Light theme overrides for browser windows */
[data-theme="light"] .browser-window {
    background: #ffffff;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15),
                0 0 0 1px rgba(32, 78, 126, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.9);
}

[data-theme="light"] .app-preview-container:hover .browser-window {
    box-shadow: 0 30px 80px rgba(32, 78, 126, 0.15),
                0 0 0 1px rgba(32, 78, 126, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.95);
}

[data-theme="light"] .browser-header {
    background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom-color: rgba(32, 78, 126, 0.1);
}

[data-theme="light"] .browser-url {
    background: rgba(0, 0, 0, 0.05);
    color: rgba(30, 30, 40, 0.6);
}

[data-theme="light"] .browser-content {
    background: #ffffff;
}

[data-theme="light"] .project-preview {
    background: #ffffff;
}

[data-theme="light"] .kanban-column {
    background: rgba(32, 78, 126, 0.02);
    border-color: rgba(32, 78, 126, 0.1);
}

[data-theme="light"] .kanban-header {
    color: rgba(30, 30, 40, 0.7);
    border-bottom-color: rgba(32, 78, 126, 0.1);
}

[data-theme="light"] .kanban-task {
    background: rgba(32, 78, 126, 0.02);
    border-color: rgba(32, 78, 126, 0.1);
}

[data-theme="light"] .kanban-task:hover {
    background: rgba(32, 78, 126, 0.08);
    border-color: rgba(32, 78, 126, 0.3);
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const browserWindows = document.querySelectorAll('.browser-window');
    
    browserWindows.forEach(window => {
        const container = window.closest('.app-preview-container, .hero-preview');
        
        if (container) {
            container.addEventListener('mousemove', function(e) {
                const rect = container.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                
                const rotateX = ((y - centerY) / centerY) * -5;
                const rotateY = ((x - centerX) / centerX) * 5;
                
                if (window.classList.contains('hero-browser')) {
                    window.style.transform = `translateZ(0) rotateX(${rotateX + 2}deg) rotateY(${rotateY - 2}deg)`;
                } else {
                    window.style.transform = `translateY(-15px) translateZ(20px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
                }
            });
            
            container.addEventListener('mouseleave', function() {
                if (window.classList.contains('hero-browser')) {
                    window.style.transform = 'translateZ(0) rotateX(2deg) rotateY(-2deg)';
                } else {
                    window.style.transform = 'translateZ(0) rotateX(2deg)';
                }
            });
        }
    });
});
</script>
@endpush
@endsection

