<aside class="main-sidebar">
  {{-- Sidebar Header / School Acronym --}}
  <h3 style="color:aliceblue; text-align: center; margin-top: 15px; margin-bottom: 15px; font-weight: 300; letter-spacing: 1px;">
    {{ env('SCHOOL_ACRONYM', 'ACADEMY') }} RECEPTION
  </h3>

  <section class="sidebar">
    {{-- User Panel --}}
    <div class="user-panel">
      <div class="pull-left image">
        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=00a65a&color=fff" class="img-circle" alt="User Image">
      </div>
      <div class="pull-left info">
        <p>{{ Auth::user()->name }}</p>
        <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
      </div>
    </div>

    <ul class="sidebar-menu" data-widget="tree">
      <li class="header">MAIN NAVIGATION</li>

      {{-- Dashboard --}}
      <li class="{{ Request::is('receptionist/dashboard') ? 'active' : '' }}">
        <a href="{{ route('receptionist.dashboard') }}">
          <i class="fa fa-dashboard"></i> <span>Dashboard</span>
        </a>
      </li>

      {{-- Student Management --}}
      <li class="treeview {{ Request::is('receptionist/students*') ? 'active menu-open' : '' }}">
        <a href="#">
          <i class="fa fa-users"></i> <span>Student Directory</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu">
          <li class="{{ Request::is('receptionist/students') ? 'active' : '' }}">
            <a href="{{ route('fees.report') }}">
              <i class="fa fa-list"></i> Financial Report
            </a>
          </li>
          <li class="{{ Request::is('receptionist/students/create') ? 'active' : '' }}">
            <a href="{{ route('students.create') }}">
              <i class="fa fa-user-plus"></i> New Admission
            </a>
          </li>
        </ul>
      </li>

      {{-- Finance & Payments --}}
      <li class="treeview {{ Request::is('receptionist/payments*') ? 'active menu-open' : '' }}">
        <a href="#">
          <i class="fa fa-money"></i> <span>Fees & Payments</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu">
          <li class="{{ Request::is('receptionist/payments/create') ? 'active' : '' }}">
            <a href="{{ route('fees.create') }}">
              <i class="fa fa-plus"></i> Collect Payment
            </a>
          </li>
          <li class="{{ Request::is('receptionist/payments') ? 'active' : '' }}">
            <a href="{{ route('fees.index') }}">
              <i class="fa fa-history"></i> Payment Logs
            </a>
          </li>
        </ul>
      </li>

      {{-- Classes & Enrollment --}}
      <li class="{{ Request::is('receptionist/classes*') ? 'active' : '' }}">
        <a href="{{ route('receptionist.classes.index') }}">
          <i class="fa fa-university"></i> <span>Class Lists</span>
        </a>
      </li>

      <li class="header">FRONT DESK</li>
      {{-- Visitor Management (Placeholder for future) --}}
      <li class="{{ Request::is('receptionist/visitors*') ? 'active' : '' }}">
        <a href="#">
          <i class="fa fa-address-book"></i> <span>Visitor Log</span>
          <span class="pull-right-container">
            <small class="label pull-right bg-green">New</small>
          </span>
        </a>
      </li>

      <li class="header">ACCOUNT SETTINGS</li>

      <li class="{{ Request::is('receptionist/profile*') ? 'active' : '' }}">
        <a href="{{ route('receptionist.profile') }}">
          <i class="fa fa-user"></i> <span>My Profile</span>
        </a>
      </li>

      <li>
        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
          <i class="fa fa-sign-out text-red"></i> <span class="text-red">Sign Out</span>
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
          @csrf
        </form>
      </li>
    </ul>
  </section>
</aside>
