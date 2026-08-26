<aside class="main-sidebar" style="background-color: #1e293b; border-right: 1px solid #334155;">
  {{-- Modern School Title --}}
  <div style="padding: 25px 15px; background: #0f172a; border-bottom: 1px solid #1e293b;">
    <h3 style="color: #6366f1; text-align: center; margin: 0; font-weight: 800; letter-spacing: 2px; font-size: 18px; text-transform: uppercase;">
      {{ env('SCHOOL_ACRONYM', 'ACADEMY') }}
    </h3>
  </div>

  <section class="sidebar">
    <ul class="sidebar-menu" data-widget="tree" style="margin-top: 10px;">
      <li class="header" style="color: #64748b; background: transparent; padding: 15px 25px 10px; font-size: 11px; font-weight: 700; letter-spacing: 1px;">MAIN NAVIGATION</li>

      {{-- Dashboard: Admin vs Teacher --}}
      @if(auth()->user()->role === 'admin')
      <li class="{{ Request::is('dashboard*') ? 'active' : '' }}">
        <a href="{{ route('dashboard') }}" style="padding: 12px 20px; border-left-color: #6366f1;">
          <i class="fa fa-th-large" style="color: #818cf8;"></i> <span style="font-weight: 600;">Admin Dashboard</span>
        </a>
      </li>
      @else
      <li class="{{ Request::is('teacher/dashboard*') ? 'active' : '' }}">
        <a href="{{ route('teacher.dashboard') }}" style="padding: 12px 20px; border-left-color: #6366f1;">
          <i class="fa fa-television" style="color: #818cf8;"></i> <span style="font-weight: 600;">Teacher Portal</span>
        </a>
      </li>
      @endif

      {{-- Payroll Module (Admin Only) --}}
      <li class="treeview {{ Request::is('payroll*') ? 'active' : '' }}">
        <a href="#">
          <i class="fa fa-calculator" style="color: #fbbf24;"></i> <span style="font-weight: 600;">Payroll</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu" style="background: #0f172a;">
          <li class="{{ Request::is('payroll') ? 'active' : '' }}">
            <a href="{{ route('payroll.index') }}" style="padding-left: 30px;"><i class="fa fa-circle-o"></i> Manage Payroll</a>
          </li>
        </ul>
      </li>

      @if(auth()->user()->role === 'admin')
      {{-- NEW: Manage Staff (Admin, Teacher, Receptionist) --}}
      <li class="treeview {{ Request::is('teachers*') ? 'active' : '' }}">
        <a href="#">
          <i class="fa fa-user-secret" style="color: #38bdf8;"></i> <span style="font-weight: 600;">Manage Staff</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu" style="background: #0f172a;">
          <li class="{{ Request::is('teachers/create') ? 'active' : '' }}">
            <a href="{{ route('teachers.create') }}" style="padding-left: 30px;"><i class="fa fa-plus-circle"></i> Add New Staff</a>
          </li>
          <li class="{{ Request::is('teachers') ? 'active' : '' }}">
            <a href="{{ route('teachers.index') }}" style="padding-left: 30px;"><i class="fa fa-list-ul"></i> View All Staff</a>
          </li>
        </ul>
      </li>

      {{-- Students (Admin Only) --}}
      <li class="treeview {{ Request::is('students*') ? 'active' : '' }}">
        <a href="#">
          <i class="fa fa-group" style="color: #c084fc;"></i> <span style="font-weight: 600;">Students</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu" style="background: #0f172a;">
          <li class="{{ Request::is('students/create') ? 'active' : '' }}"><a href="{{ route('students.create') }}" style="padding-left: 30px;"><i class="fa fa-circle-o"></i> Add Student</a></li>
          <li class="{{ Request::is('students') ? 'active' : '' }}"><a href="{{ route('students.index') }}" style="padding-left: 30px;"><i class="fa fa-circle-o"></i> Manage Students</a></li>
          <li class="{{ Request::is('students/stats*') ? 'active' : '' }}"><a href="{{ route('students.enrollment_stats') }}" style="padding-left: 30px;"><i class="fa fa-circle-o"></i> Enrollment Stats</a></li>
          <li class="{{ Request::is('students/promote*') ? 'active' : '' }}"><a href="{{ route('students.promote') }}" style="padding-left: 30px;"><i class="fa fa-circle-o"></i> Promote Students</a></li>
        </ul>
      </li>

      {{-- Admissions (Admin Only) --}}
      <li class="treeview {{ Request::is('admissions*') ? 'active' : '' }}">
        <a href="#">
          <i class="fa fa-user-plus" style="color: #10b981;"></i> <span style="font-weight: 600;">Admissions</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu" style="background: #0f172a;">
          <li class="{{ Request::is('admissions') ? 'active' : '' }}">
            <a href="{{ route('admissions.manage') }}" style="padding-left: 30px;"><i class="fa fa-check-square"></i> Manage Applications</a>
          </li>
        </ul>
      </li>

      {{-- Classes (Admin Only) --}}
      <li class="treeview {{ Request::is('classes*') ? 'active' : '' }}">
        <a href="#">
          <i class="fa fa-institution" style="color: #f43f5e;"></i> <span style="font-weight: 600;">Classes</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu" style="background: #0f172a;">
          <li class="{{ Request::is('classes') ? 'active' : '' }}">
            <a href="{{ route('classes.index') }}" style="padding-left: 30px;"><i class="fa fa-circle-o"></i> Manage Classes</a>
          </li>
          <li class="{{ Request::is('classes/*/assign*') ? 'active' : '' }}">
            <a href="{{ route('classes.index') }}" style="padding-left: 30px;"><i class="fa fa-circle-o"></i> Assign Teachers</a>
          </li>
          <li class="{{ Request::is('classes/subjects*') ? 'active' : '' }}">
            <a href="{{ route('classes.assign') }}" style="padding-left: 30px;"><i class="fa fa-circle-o"></i> Assign Subjects</a>
          </li>
        </ul>
      </li>

      {{-- TIMETABLE (Admin Only) --}}
      <li class="treeview {{ Request::is('admin/timetable*') ? 'active' : '' }}">
        <a href="#">
          <i class="fa fa-calendar" style="color: #00ea61;"></i> <span style="font-weight: 600;">Timetable</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu" style="background: #0f172a;">
          <li class="{{ Request::is('admin/timetable') ? 'active' : '' }}">
            <a href="{{ route('timetable.index') }}" style="padding-left: 30px;"><i class="fa fa-list-alt"></i> Class Timetables</a>
          </li>
          <li class="{{ Request::is('admin/timetable/create') ? 'active' : '' }}">
            <a href="{{ route('timetable.create') }}" style="padding-left: 30px;"><i class="fa fa-plus"></i> Add New Slot</a>
          </li>
        </ul>
      </li>

      {{-- Subjects (Admin Only) --}}
      <li class="treeview {{ Request::is('subjects*') ? 'active' : '' }}">
        <a href="#">
          <i class="fa fa-graduation-cap" style="color: #6366f1;"></i> <span style="font-weight: 600;">Subjects</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu" style="background: #0f172a;">
          <li class="{{ Request::is('subjects') ? 'active' : '' }}"><a href="{{ route('subjects.index') }}" style="padding-left: 30px;"><i class="fa fa-circle-o"></i> Manage Subjects</a></li>
        </ul>
      </li>

      {{-- Terms (Admin Only) --}}
      <li class="treeview {{ Request::is('terms*') ? 'active' : '' }}">
        <a href="#">
          <i class="fa fa-navicon" style="color: #94a3b8;"></i> <span style="font-weight: 600;">Semesters/Terms</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu" style="background: #0f172a;">
          <li class="{{ Request::is('terms') ? 'active' : '' }}"><a href="{{ route('terms.index') }}" style="padding-left: 30px;"><i class="fa fa-circle-o"></i> Manage Terms</a></li>
        </ul>
      </li>
      @endif

      {{-- Shared: Attendance (Both Roles) --}}
      <li class="treeview {{ Request::is('attendance*') ? 'active' : '' }}">
        <a href="#">
          <i class="fa fa-check-square" style="color: #22c55e;"></i> <span style="font-weight: 600;">Attendance</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu" style="background: #0f172a;">
          <li><a href="#" style="padding-left: 30px;"><i class="fa fa-circle-o"></i> Mark Attendance</a></li>
          <li><a href="#" style="padding-left: 30px;"><i class="fa fa-circle-o"></i> View Attendance</a></li>
        </ul>
      </li>

      {{-- Shared: Exams (Both Roles) --}}
      <li class="treeview {{ Request::is('exams*') || Request::is('marks*') || Request::is('teacher/exams*') ? 'active' : '' }}">
        <a href="#">
          <i class="fa fa-hourglass" style="color: #ec4899;"></i> <span style="font-weight: 600;">Exams</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu" style="background: #0f172a;">
          <li class="{{ Request::is('exams') ? 'active' : '' }}">
            <a href="{{ route('exams.index') }}" style="padding-left: 30px;">
              <i class="fa fa-circle-o"></i> Exam Schedule
            </a>
          </li>

          {{-- NEW: Gatekeeper direct jump link --}}
        <li class="{{ Request::is('teacher/exams/*/verify') ? 'active' : '' }}">
        <a href="{{ route('exams.index') }}" style="padding-left: 30px;">
            <i class="fa fa-user-secret text-success"></i> Biometric Gatekeeper
        </a>
        </li>

          <li><a href="#" style="padding-left: 30px;"><i class="fa fa-circle-o"></i> Exams Report</a></li>
        </ul>
      </li>

      {{-- Continuous Assessment: daily class activity marks, independent of Exams above --}}
      <li class="treeview {{ Request::is('activities*') || Request::is('teacher/activities*') ? 'active menu-open' : '' }}">
        <a href="#" style="padding: 12px 20px;">
          <i class="fa fa-tasks" style="color: #22c55e;"></i> <span style="font-weight: 600;">Continuous Assessment</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu" style="background: #0f172a;">
          <li class="{{ Request::is('activities') ? 'active' : '' }}">
            <a href="{{ route('activities.index') }}" style="padding-left: 30px;">
              <i class="fa fa-circle-o"></i> All Logged Activities
            </a>
          </li>
          <li class="{{ Request::is('activities/analytics') ? 'active' : '' }}">
            <a href="{{ route('activities.analytics') }}" style="padding-left: 30px;">
              <i class="fa fa-bar-chart"></i> Analytics & AI Insights
            </a>
          </li>
        </ul>
      </li>

      @if(auth()->user()->role === 'admin')
      {{-- Fees (Admin Only) --}}
      <li class="treeview {{ Request::is('fees*') ? 'active' : '' }}">
        <a href="#">
          <i class="fa fa-money" style="color: #10b981;"></i> <span style="font-weight: 600;">Fees</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu" style="background: #0f172a;">
          <li class="{{ Request::is('fees/create') ? 'active' : '' }}"><a href="{{ route('fees.create') }}" style="padding-left: 30px;"><i class="fa fa-circle-o"></i> Make Payment</a></li>
          <li class="{{ Request::is('fees') ? 'active' : '' }}"><a href="{{ route('fees.index') }}" style="padding-left: 30px;"><i class="fa fa-circle-o"></i> View Payments</a></li>
          <li class="{{ Request::is('fees/structure') ? 'active' : '' }}"><a href="{{ route('fees.structure') }}" style="padding-left: 30px;"><i class="fa fa-gears"></i> Fee Setup</a></li>
          <li class="{{ Request::is('fees/report') ? 'active' : '' }}"><a href="{{ route('fees.report') }}" style="padding-left: 30px;"><i class="fa fa-circle-o"></i> Payment Report</a></li>
        </ul>
      </li>

      {{-- Expenses (Admin Only) --}}
      <li class="treeview {{ Request::is('expenses*') ? 'active' : '' }}">
        <a href="#">
          <i class="fa fa-credit-card" style="color: #f87171;"></i> <span style="font-weight: 600;">Expenses</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu" style="background: #0f172a;">
          <li class="{{ Request::is('expenses/create') ? 'active' : '' }}"><a href="{{ route('expenses.create') }}" style="padding-left: 30px;"><i class="fa fa-circle-o"></i> Add Expense</a></li>
          <li class="{{ Request::is('expenses') ? 'active' : '' }}"><a href="{{ route('expenses.index') }}" style="padding-left: 30px;"><i class="fa fa-circle-o"></i> Manage Expenses</a></li>
        </ul>
      </li>

      {{-- Receptionist Portal (Specific Routes) --}}
      <li class="treeview {{ Request::is('receptionist*') ? 'active' : '' }}">
        <a href="#">
          <i class="fa fa-briefcase" style="color: #38bdf8;"></i> <span style="font-weight: 600;">Receptionist Portal</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu" style="background: #0f172a;">
          <li class="{{ Request::is('receptionist/students*') ? 'active' : '' }}">
            <a href="{{ route('receptionist.students.index') }}" style="padding-left: 30px;"><i class="fa fa-circle-o"></i> Student List</a>
          </li>
          <li class="{{ Request::is('receptionist/payments/create*') ? 'active' : '' }}">
            <a href="{{ route('fees.create') }}" style="padding-left: 30px;"><i class="fa fa-circle-o"></i> Process Payment</a>
          </li>
        </ul>
      </li>
      @endif

      <li class="{{ Request::is('inventory*') ? 'active' : '' }}">
        <a href="{{ route('inventory.index') }}" style="padding: 12px 20px;">
          <i class="fa fa-archive" style="color: #f97316;"></i> <span style="font-weight: 600;">Inventory</span>
        </a>
      </li>

      {{-- Settings --}}
      <li class="treeview {{ Request::is('admin/settings*') || Request::is('settings*') || Request::is('teacher/profile*') ? 'active' : '' }}">
        <a href="#">
          <i class="fa fa-gear" style="color: #94a3b8;"></i> <span style="font-weight: 600;">Settings</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu" style="background: #0f172a;">
          <li>
            <a href="{{ auth()->user()->role === 'admin' ? route('admin.profile') : route('teacher.profile') }}" style="padding-left: 30px;">
              <i class="fa fa-user"></i> Edit Profile
            </a>
          </li>
          <li class="{{ Request::is('*/change-password') ? 'active' : '' }}">
            <a href="{{ route('admin.change_password') }}" style="padding-left: 30px;">
              <i class="fa fa-lock text-aqua"></i> Change Password
            </a>
          </li>
        </ul>
      </li>

      {{-- Logout --}}
      <li style="margin-top: 20px; border-top: 1px solid #334155;">
        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="color: #f87171 !important;">
          <i class="fa fa-sign-out"></i> <span style="font-weight: 600;">Logout</span>
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
          @csrf
        </form>
      </li>
    </ul>
  </section>
</aside>
