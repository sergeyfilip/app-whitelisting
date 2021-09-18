Name: app-whitelisting
Epoch: 1
Version: 2.0.0
Release: 2%{dist}
Summary: gateway whitelisting
License: RedPiranha
Group: CrystalEye/Apps
Packager: RedPiranha
Vendor: RedPiranha
Source: %{name}-%{version}.tar.gz
Buildarch: noarch
Requires: %{name}-core = 1:%{version}-%{release}
Requires: app-base
Requires: jparser >= 2.1.1
%description
gateway whitelisting.

%package core
Summary:  whitelisting
License: RedPiranha
Group: CrystalEye/Apps
Requires: app-base-core
Requires: app-intrusion-detection


%description core
 whitelisting.

 whitelisting app.

%prep
%setup -q
%build

%install
mkdir -p -m 755 %{buildroot}/usr/crystaleye/apps/whitelisting
cp -r * %{buildroot}/usr/crystaleye/apps/whitelisting/

install -D -m 0644 packaging/app-whitelisting.cron %{buildroot}/etc/cron.d/app-whitelisting
install -D -m 0755 packaging/white_list_ind.rules %{buildroot}/etc/suricata.d/rules/redpiranha/white_list_ind.rules
install -d -m 0755 %{buildroot}/var/crystaleye/whitelisting
install -d -m 0755 %{buildroot}/var/crystaleye/whitelisting/backup
install -D -m 0755 packaging/update-fingerprints %{buildroot}/usr/sbin/update-fingerprints




%post
logger -p local6.notice -t installer 'app-whitelisting - installing'
/usr/sbin/addsudo /usr/bin/jparser app-whitelisting
/usr/sbin/addsudo /usr/bin/jreport app-whitelisting
/usr/sbin/addsudo /usr/bin/janalyzer app-whitelisting
/usr/sbin/addsudo /usr/bin/jmapper app-whitelisting
/usr/sbin/addsudo /usr/bin/jgenerator app-whitelisting
/usr/sbin/addsudo /usr/bin/jinteger app-whitelisting
/usr/sbin/addsudo /usr/bin/jmaker app-whitelisting
/usr/sbin/addsudo /usr/bin/stop_whitelisting app-whitelisting



%post core
logger -p local6.notice -t installer 'app-whitelisting-core - installing'

if [ $1 -eq 1 ]; then
    [ -x /usr/crystaleye/apps/whitelisting/deploy/install ] && /usr/crystaleye/apps/whitelisting/deploy/install
fi

[ -x /usr/crystaleye/apps/whitelisting/deploy/upgrade ] && /usr/crystaleye/apps/whitelisting/deploy/upgrade

exit 0

%preun
uninstall_yaml
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-whitelisting - uninstalling'
fi

%preun core
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-whitelisting-core - uninstalling'
    [ -x /usr/crystaleye/apps/whitelisting/deploy/uninstall ] && /usr/crystaleye/apps/whitelisting/deploy/uninstall
fi

exit 0

%files
%defattr(-,root,root)
/usr/crystaleye/apps/whitelisting/controllers
/usr/crystaleye/apps/whitelisting/htdocs
/usr/crystaleye/apps/whitelisting/views


%files core
%defattr(-,root,root)
%exclude /usr/crystaleye/apps/whitelisting/packaging
%dir /usr/crystaleye/apps/whitelisting
%dir /var/crystaleye/whitelisting
%dir /var/crystaleye/whitelisting/backup
/usr/crystaleye/apps/whitelisting/deploy
/usr/crystaleye/apps/whitelisting/language
/usr/crystaleye/apps/whitelisting/libraries
/usr/sbin/update-fingerprints
/etc/cron.d/app-whitelisting
/etc/suricata.d/rules/redpiranha/white_list_ind.rules

%changelog
* Mon Jan 18 2021 Sergey Filipovich <sergey@redpiranha.net> 2.0.0-2
- Codestyle corrections
* Mon Jan 11 2021 Sergey Filipovich <sergey@redpiranha.net> 2.0.0-1
- New menu tab was added (individual fingerprints)
- New function added
- jparser was changed accordinly
* Fri Jan 1 2021 Jaymin Patel <jaymin@redpiranha.net> 1.8.1-1
- Codestyle corrections
* Wed Dec 02 2020 Sergey Filipovich <sergey@redpiranha.net> 1.8.0-1
- init scripts were removed
- jparser was changed accordinly
* Mon Oct 26 2020 Sergey Filipovich <sergey@redpiranha.net> 1.7.0-1
- start/stop scripts corrected
- cron job for updating fingerprints was implemented
- auto start was implemented
- jparser was changed accordinly
* Wed Sep 23 2020 Sergey Filipovich <sergey@redpiranha.net> 1.6.0-2
- Issue with valid names was corrected
- Policy management logic was corrected
- jparser was changed accordinly
* Mon Sep 07 2020 Sergey Filipovich <sergey@redpiranha.net> 1.6.0-1
- Workflow logical error was corrected, some bugs fixed
- Bug 3223 was corrected
- jparser was changed accordinly
* Fri Jul 24 2020 Sergey Filipovich <sergey@redpiranha.net> 1.5.0-1
- Changes in rules generation, in functionality
- jparser changed accordinly
* Mon Jun 8 2020 Sergey Filipovich <sergey@redpiranha.net> 1.4.0-1
- Changes in install
- jparser changed accordinly
* Wed May 27 2020 Sergey Filipovich <sergey@redpiranha.net> 1.3.0-1
- Install suricata.yaml functionality corrected
- jparser changed accordinly
* Fri Mar 27 2020 Sergey Filipovich <sergey@redpiranha.net> 1.2.0-1
- Update functionality added
- jparser changed accordinly
* Mon Sep 23 2019 Sergey Filipovich <sergey@redpiranha.net> 1.1.0-1
- New tabs added, new functions
- jparser changed accordinly
* Wed Jun 26 2019 Sergey Filipovich <sergey@redpiranha.net> 1.0.8-4
- Pass - reject bug resolved
- jparser changed accordinly
* Tue Jun 4 2019 Sergey Filipovich <sergey@redpiranha.net> 1.0.8-4
- Akk rules were changed to reject type
- Task:593 and Adam request
* Tue Mar 12 2019 Rajesh Kumar <rk@redpiranha.net> 1.0.8-3
- GUI changes related with service start and stop
- Dlg box redesign for showing fingerprint and msg improvement.
- Task:593
* Sat Mar 02 2019 Rajesh Kumar <rk@redpiranha.net> 1.0.8-2
- GUI changes due to toggle app details box.
- Empty row in rules table.


