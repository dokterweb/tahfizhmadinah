<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="/dashboard" class="brand-link text-center">
      {{-- <img src="dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8"> --}}
      <span class="brand-text font-weight-light">{{env('APP_NAME')}}</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          {{-- <img src="dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image"> --}}
        </div>
        <div class="info">
          <a href="#" class="d-block">{{auth()->user()->name}}</a>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          <li class="nav-item">

            @if(auth()->user()->hasRole('admin'))

                <a href="{{ route('dashboard') }}"
                  class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">

                    <i class="nav-icon fas fa-tachometer-alt"></i>

                    <p>Dashboard</p>

                </a>

            @elseif(auth()->user()->hasRole('ustadz'))

                <a href="{{ route('ustadz.dashboard') }}"
                  class="nav-link {{ request()->routeIs('ustadz.dashboard') ? 'active' : '' }}">

                    <i class="nav-icon fas fa-tachometer-alt"></i>

                    <p>Dashboard</p>

                </a>

            @elseif(auth()->user()->hasRole('siswa'))

                <a href="{{ route('siswa.dashboard') }}"
                  class="nav-link {{ request()->routeIs('siswa.dashboard') ? 'active' : '' }}">

                    <i class="nav-icon fas fa-tachometer-alt"></i>

                    <p>Dashboard</p>

                </a>

            @endif

        </li>
          @role('admin')
           <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-bars"></i>
              <p>Master<i class="right fas fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{route('siswas.index')}}" class="nav-link {{request()->routeIs('siswas.index')?'active':''}}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Siswa</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{route('ustadzs.index')}}" class="nav-link {{request()->routeIs('ustadzs.index')?'active':''}}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Ustadz</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{route('kelasnyas')}}" class="nav-link {{request()->routeIs('kelasnyas')?'active':''}}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Kelas</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{route('sub_kelas.index')}}" class="nav-link {{request()->routeIs('sub_kelas.index')?'active':''}}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Sub Kelas</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{route('absensis')}}" class="nav-link {{request()->routeIs('absensis')?'active':''}}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Absensi</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{route('settings.school.edit')}}" class="nav-link {{request()->routeIs('settings.school.edit')?'active':''}}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Profile Sekolah</p>
                </a>
              </li>
            </ul>
          </li> 
          
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-book-open" style="color: rgb(99, 230, 190);"></i>
              <p>Sabaq<i class="right fas fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{route('sabaqs')}}" class="nav-link {{request()->routeIs('sabaqs')?'active':''}}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Input Sabaq</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{route('sabaqs.laporan')}}" class="nav-link {{request()->routeIs('sabaqs.laporan')?'active':''}}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Laporan Sabaq</p>
                </a>
              </li>
            </ul>
          </li> 

          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-book-open" style="color: rgb(255, 212, 59);"></i>
              <p>Sabqi<i class="right fas fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{route('sabqis')}}" class="nav-link {{request()->routeIs('sabqis')?'active':''}}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Input Sabqi</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{route('sabqis.laporan')}}" class="nav-link {{request()->routeIs('sabqis.laporan')?'active':''}}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Laporan Sabqi</p>
                </a>
              </li>
            </ul>
          </li> 

          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-book-open" style="color: rgb(231, 116, 252);"></i>
              <p>Manzil<i class="right fas fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{route('manzils')}}" class="nav-link {{request()->routeIs('manzils')?'active':''}}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Input Manzil</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{route('manzils.laporan')}}" class="nav-link {{request()->routeIs('manzils.laporan')?'active':''}}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Laporan Manzil</p>
                </a>
              </li>
            </ul>
          </li> 
          @endrole
          @role('ustadz')
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-book-open" style="color: rgb(99, 230, 190);"></i>
              <p>Sabaq<i class="right fas fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{route('sabaqs')}}" class="nav-link {{request()->routeIs('sabaqs')?'active':''}}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Input Sabaq</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{route('sabaqs.laporan')}}" class="nav-link {{request()->routeIs('sabaqs.laporan')?'active':''}}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Laporan Sabaq</p>
                </a>
              </li>
            </ul>
          </li> 

          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-book-open" style="color: rgb(255, 212, 59);"></i>
              <p>Sabqi<i class="right fas fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{route('sabqis')}}" class="nav-link {{request()->routeIs('sabqis')?'active':''}}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Input Sabqi</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{route('sabqis.laporan')}}" class="nav-link {{request()->routeIs('sabqis.laporan')?'active':''}}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Laporan Sabqi</p>
                </a>
              </li>
            </ul>
          </li> 

          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-book-open" style="color: rgb(231, 116, 252);"></i>
              <p>Manzil<i class="right fas fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{route('manzils')}}" class="nav-link {{request()->routeIs('manzils')?'active':''}}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Input Manzil</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{route('manzils.laporan')}}" class="nav-link {{request()->routeIs('manzils.laporan')?'active':''}}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Laporan Manzil</p>
                </a>
              </li>
            </ul>
          </li> 
{{-- 
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Iqro<i class="right fas fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{route('iqros')}}" class="nav-link {{request()->routeIs('iqros')?'active':''}}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Input Iqro</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{route('iqros.laporan')}}" class="nav-link {{request()->routeIs('iqros.laporan')?'active':''}}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Laporan Iqro</p>
                </a>
              </li>
            </ul>
          </li> 
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Keuangan<i class="right fas fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{route('periodes')}}" class="nav-link {{request()->routeIs('periodes')?'active':''}}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Periode</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{route('posnyas')}}" class="nav-link {{request()->routeIs('posnyas')?'active':''}}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>POS</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{route('payments')}}" class="nav-link {{request()->routeIs('payments')?'active':''}}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Jenis Bayar</p>
                </a>
              </li>
            </ul>
          </li> 
 --}}
          
          <li class="nav-item">
            <a href="{{route('absensis.ustadzIndex')}}" class="nav-link {{request()->routeIs('absensis.ustadzIndex')?'active':''}}">
              <i class="nav-icon far fa-bell"></i>
              <p>Absensi for Ustadz</p>
            </a>
          </li>
          @endrole
          @role('siswa')
          <li class="nav-item">
            <a href="{{route('sabaqs.sabaqsiswa')}}" class="nav-link {{request()->routeIs('sabaqs.sabaqsiswa')?'active':''}}">
              <i class="nav-icon fas fa-book-open" style="color: rgb(99, 230, 190);"></i>
              <p>Sabaq</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{route('sabqis.sabqisiswa')}}" class="nav-link {{request()->routeIs('sabqis.sabqisiswa')?'active':''}}">
              <i class="nav-icon fas fa-book-open" style="color: rgb(255, 212, 59);"></i>
              <p>Sabqi</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{route('manzils.manzilsiswa')}}" class="nav-link {{request()->routeIs('manzils.manzilsiswa')?'active':''}}">
              <i class="nav-icon fas fa-book-open" style="color: rgb(231, 116, 252);"></i>
              <p>Manzil</p>
            </a>
          </li>
          @endrole
         {{--  <li class="nav-item">
            <a href="{{route('iqros.iqrosiswa')}}" class="nav-link {{request()->routeIs('iqros.iqrosiswa')?'active':''}}">
              <i class="nav-icon fas fa-th"></i>
              <p>Iqro</p>
            </a>
          </li> 
          <li class="nav-item">
            <a href="{{route('payoutsiswas.index')}}" class="nav-link {{request()->routeIs('payoutsiswas.index')?'active':''}}">
              <i class="nav-icon fas fa-th"></i>
              <p>Pembayaran</p>
            </a>
          </li>--}}

          <li class="nav-item">
            <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                <i class="nav-icon fas fa-user"></i>
                <p>Profile</p>
            </a>
          </li>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>