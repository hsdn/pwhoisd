## PHP Whois Server Daemon

Simple and very performance Whois-server written on PHP, using multi-threaded architecture. There is support for database MySQL and flexible configuration of the data output format. There is also a good access control system based on ACL.

### General Features
- Multi-threaded architecture based on the functions PCNTL.
- Storing data in a database MySQL (one or more data tables).
- Full supports IPv6 protocol.
- Powerful control system access and requests rate limits based on ACL.
- Support for formatted output using standard functions php.
- Support the use of flags in Whois requests to change the data-source or data output format.

For complete information about the functionality of the server, see file: **src/config.php**.

### System Requirements
- php version 5.4.0 and above with POSIX functions support.
- php PCNTL extension.
- php Filter extension.
- php GMP or BCMATH extensions.
- php Sockets extension.

### Installation

1. Copy the file **bin/pwhoisd.phar** to your executable files directory, such as **/usr/local/sbin**.
2. Copy the configuration file **src/config.php** to your **etc/pwhoisd** directory, such as **/usr/local/etc/pwhoisd**.
3. Edit the parameters in your configuration file of pwhoisd.
4. Create the MySQL database and updload a demo table from SQL file **share/example_pwhoisd.sql**.
5. Run the server for tests using the command: **php /usr/local/sbin/pwhoisd.phar --config=/usr/local/etc/pwhoisd/config.php**.
6. Test the server by using the telnet or whois command: **whois -h 127.0.0.1 "hsdn.ru"** (you should get output as in the example below).

#### Run the Server as a Daemon
To start the server as a daemon, uses the command-line parameter **--daemon**. Optionally, you can specify the path to the PID-file by using the parameter **--pidfile=/var/run/pwhoisd.pid**.

The server can also be run using the startup rc-script. Script for FreeBSD system will be as follows:

    #!/bin/sh

    # PROVIDE: pwhoisd
    # REQUIRE: DAEMON
    # BEFORE:  LOGIN
    # KEYWORD: shutdown

    # Define these pwhoisd_* variables in one of these files:
    #       /etc/rc.conf
    #       /etc/rc.conf.local
    #
    # DO NOT CHANGE THESE DEFAULT VALUES HERE
    #
    pwhoisd_enable=${pwhoisd_enable-"NO"}

    . /etc/rc.subr

    name="pwhoisd"
    rcvar=`set_rcvar`

    load_rc_config $name

    : ${pwhoisd_enable="NO"}
    : ${pwhoisd_conf="/usr/local/etc/pwhoisd.php"}
    : ${pwhoisd_piddir="/var/run"}

    pidfile="${pwhoisd_piddir}/pwhoisd.pid"
    command_interpreter="php"
    command_args="--config=${pwhoisd_conf} --pidfile=${pidfile} --daemon"
    command="/usr/local/bin/pwhoisd.phar"

    run_rc_command "$1"

1. Copy this script into your rc.d directory **/usr/local/etc/rc.d/** with the name **pwhoisd**
2. Set the permissions: **chmod 555 /usr/local/etc/rc.d/pwhoisd**.
3. Run the server daemon using the command: **/usr/local/etc/rc.d/pwhoisd start**

Note: correct the path to the files on your right paths and do not forget to add a line **pwhoisd_enable="YES"** to **/etc/rc.conf**.

#### Server Command Line Parameters
Usage: **pwhoisd.phar** [--config=file] [--pidfile=file] [--uid=identifier] [--gid=identifier] [--daemon]
-  **--config** main configuration file
-  **--pidfile** file to save process ID
-  **--uid** specify an UID for process
-  **--gid** specify an GID for process
-  **--daemon** run process in background mode
-  **--help** show this help only

### ACL Configurations

By default all connections are allowed and no limits do not apply.

Access rule format:

    ['ACTION'(, 'MESSAGE')(, ['VARIABLE', 'OPERATOR', 'VALUE/UNIT'])(, ['VARIABLE', 'OPERATOR', 'VALUE/UNIT'])(, ...)]

ACTION:
- **allow** Permit a client connections and requests.
- **deny** Print specified message to a client (optional) and drop the connection.
- **drop** Silently drop a client connection. No messages is sent.

VARIABLE:
- **client_ip** Client connection IP address (used for IP/subnets checks).
- **requests** Used to specify a requests rate limit to the specific client connections.
- **rate** Used to specify the global server requests rate limit for all clients connections.

MESSAGE:

Here specify the name of the section in the messages array configuration.

OPERATOR:

Any one of the allowed operators. Not to be used in IP rules. The allowed operators are:
- **<** Less than.
- **==** Equal to.
- **<=** Less than or equal to.
- **>** Greater than.
- **>=** Greater than or equal to.

VALUE:

For requests rate rules specifies a numeric value with UNIT (see below).
It can also be a single IP address (subnet, in CIDR format) or an array of IP addresses (subnets).

UNIT:

Any one of the allowed units. Not to be used in IP rules. The allowed units are:
- **sec** Second(s).
- **hr** Hour(s).
- **min** Minute(s).
- **day** Day(s).

##### ACL rules example:

    'rules' =>
    [
        // Drop all connections if server rate more 20 requests per one seconds
        // This rule will work with the previous rules (sets a global server rate limits).
        ['drop', ['rate', '>', '20/sec']],

        // Allow all connections from IP 127.0.0.1.
        // It is owerride previous (bottom) 'deny' rule for this IP (limits do not works)
        ['allow', ['client_ip', '127.0.0.1'] ],

        // Deny any client if rate more 50 requests per one minute.
        ['deny', 'limit_exceeded', ['requests', '>', '50/min']],

        // Permit all connections (it is first rule).
        ['allow'],
    ],

### Demonstration

##### Example Whois output from the database in ICANN PIR/PDR format:
    Domain Name:PIR.ORG
    Domain ID:D96207-LROR
    Creation Date:1996-02-18T05:00:00Z
    Updated Date:2015-02-20T01:41:55Z
    Registry Expiry Date:2016-02-19T05:00:00Z
    Sponsoring Registrar:GoDaddy.com, LLC (R91-LROR)
    Sponsoring Registrar IANA ID:146
    WHOIS Server:
    Referral URL:
    Domain Status:serverDeleteProhibited
    Domain Status:serverTransferProhibited
    Domain Status:serverUpdateProhibited
    Registrant ID:GODA-02131674
    Registrant Name:Registration Private
    Registrant Organization:Domains By Proxy, LLC
    Registrant Street: DomainsByProxy.com
    Registrant Street: 14747 N Northsight Blvd Suite 111, PMB 309
    Registrant City:Scottsdale
    Registrant State/Province:Arizona
    Registrant Postal Code:85260
    Registrant Country:US
    Registrant Phone:+1.4806242599
    Registrant Phone Ext:
    Registrant Fax: +1.4806242598
    Registrant Fax Ext:
    Registrant Email:
    ...
    Name Server:NS1.AMS1.AFILIAS-NST.INFO
    Name Server:NS1.MIA1.AFILIAS-NST.INFO
    Name Server:NS1.SEA1.AFILIAS-NST.INFO
    Name Server:NS1.YYZ1.AFILIAS-NST.INFO
    Name Server:
    Name Server:
    Name Server:
    Name Server:
    Name Server:
    Name Server:
    Name Server:
    Name Server:
    Name Server:
    DNSSEC:signedDelegation
    DS Created 1:2010-03-26T16:52:50Z
    DS Key Tag 1:54135
    Algorithm 1:5
    Digest Type 1:1
    Digest 1:225F055ACB65C8B60AD18B3640062E8C23A5FD89
    DS Maximum Signature Life 1:1814400 seconds

##### Example Whois output from the database in RIPN format:
    % By submitting a query to RIPN's Whois Service
    % you agree to abide by the following terms of use:
    % http://www.ripn.net/about/servpol.html#3.2 (in Russian)
    % http://www.ripn.net/about/en/servpol.html#3.2 (in English).

    domain:    HSDN.RU
    nserver:   ns1.hsdn.org.
    nserver:   ns2.hsdn.org.
    nserver:   ns3.hsdn.org.
    nserver:   ns4.hsdn.org.
    state:     REGISTERED, DELEGATED, VERIFIED
    org:       OOO "Informacionnye Seti"
    registrar: NAUNET-RU
    created:   2009.02.05
    paid-till: 2016.02.05
    free-date: 2016.03.07
    source:    TCI

    Last updated on 2015.04.20 05:01:51 MSK

### License
    PHP Whois Server Daemon

    The MIT License (MIT)

    Copyright (c) 2015 Information Networks, Ltd.

    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to deal
    in the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in all
    copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
    SOFTWARE.
