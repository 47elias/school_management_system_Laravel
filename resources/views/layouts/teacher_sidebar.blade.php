<aside class="main-sidebar">
  {{-- Sidebar Header / School Acronym --}}
  <h3 style="color:aliceblue; text-align: center; margin-top: 15px; margin-bottom: 15px; font-weight: 300; letter-spacing: 1px;">
    {{ env('SCHOOL_ACRONYM', 'ACADEMY') }} STAFF
  </h3>

  <section class="sidebar">
    {{-- User Panel --}}
    <div class="user-panel">
      <div class="pull-left image">
        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=3c8dbc&color=fff" class="img-circle" alt="User Image">
      </div>
      <div class="pull-left info">
        <p>{{ Auth::user()->name }}</p>
        <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
      </div>
    </div>

    <ul class="sidebar-menu" data-widget="tree">
      <li class="header">MAIN NAVIGATION</li>

      {{-- Dashboard --}}
      <li class="{{ Request::is('teacher/dashboard') ? 'active' : '' }}">
        <a href="{{ route('teacher.dashboard') }}">
          <i class="fa fa-dashboard"></i> <span>Dashboard</span>
        </a>
      </li>

      {{-- My Students List --}}
      <li class="{{ Request::is('teacher/my-class*') ? 'active' : '' }}">
        <a href="{{ route('teacher.my_class') }}">
          <i class="fa fa-users"></i> <span>My Students</span>
          <span class="pull-right-container">
            <small class="label pull-right bg-blue">
                @php
                    $myClassId = \App\Models\SchoolClass::where('teacher_id', Auth::id())->first()->id ?? null;
                    echo $myClassId ? \App\Models\Student::where('class_id', $myClassId)->count() : 0;
                @endphp
            </small>
          </span>
        </a>
      </li>

      {{-- My Subject Load --}}
      <li class="{{ Request::is('teacher/subjects*') ? 'active' : '' }}">
        <a href="{{ route('teacher.subjects') }}">
          <i class="fa fa-book"></i> <span>My Subjects</span>
        </a>
      </li>

      {{-- Academic Management --}}
      <li class="treeview {{ Request::is('teacher/exams*') || Request::is('teacher/marks*') ? 'active menu-open' : '' }}">
        <a href="#">
          <i class="fa fa-edit"></i> <span>Academic Management</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu">
          {{-- Continous Assessment: independent of Exams, no fixed schedule --}}
          <li class="{{ Request::is('teacher/activities*') ? 'active' : '' }}">
            <a href="{{ route('teacher.activities.index') }}">
              <i class="fa fa-tasks"></i> <span>Continuous Assessment</span>
            </a>
          </li>
          {{-- Shows only exams for subjects this teacher teaches --}}
          <li class="{{ Request::is('teacher/exams*') ? 'active' : '' }}">
            <a href="{{ route('teacher.exams.index') }}">
              <i class="fa fa-calendar"></i> Exam Schedule
            </a>
          </li>
        </ul>
      </li>

      <li class="header">COMMUNICATION</li>
      <li>
        <a href="#">
          <i class="fa fa-envelope"></i> <span>Messages</span>
          <span class="pull-right-container">
            <small class="label pull-right bg-yellow">0</small>
          </span>
        </a>
      </li>

      <li class="header">ACCOUNT SETTINGS</li>

      <li class="{{ Request::is('teacher/profile*') ? 'active' : '' }}">
        <a href="{{ route('teacher.profile') }}">
          <i class="fa fa-gears"></i> <span>My Account</span>
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
