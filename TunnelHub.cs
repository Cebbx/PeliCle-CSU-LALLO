using System;
using System.Drawing;
using System.Diagnostics;
using System.IO;
using System.Text.RegularExpressions;
using System.Threading;
using System.Windows.Forms;

namespace PeliCleTunnelHub
{
    static class Program
    {
        [STAThread]
        static void Main()
        {
            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);
            Application.Run(new MainForm());
        }
    }

    public class MainForm : Form
    {
        private string projectDir = @"C:\Users\Acer\Desktop\IMPORT FILES\flowchart\group-3";
        private string cloudflaredExe;
        private Process tunnelProcess;
        private string currentUrl = "";

        // UI Controls
        private Label lblHeaderTitle;
        private Label lblHeaderSubtitle;
        private Label lblStatusMysql;
        private Label lblStatusHerd;
        private Label lblStatusTunnel;

        private TextBox txtUrl;
        private Button btnCopyUrl;
        private Button btnOpenUrl;
        private Button btnRestartTunnel;

        private Button btnOpenEmployee;
        private Button btnCopyEmployee;
        private Button btnOpenAdmin;
        private Button btnCopyAdmin;
        private Button btnOpenGuard;
        private Button btnCopyGuard;

        private RichTextBox rtbLogs;
        private NotifyIcon trayIcon;
        private ContextMenuStrip trayMenu;

        private Color bgDark = Color.FromArgb(15, 23, 42);       // Slate 900
        private Color cardBg = Color.FromArgb(30, 41, 59);       // Slate 800
        private Color cardBorder = Color.FromArgb(51, 65, 85);   // Slate 700
        private Color textPrimary = Color.FromArgb(248, 250, 252);
        private Color textSecondary = Color.FromArgb(148, 163, 184);
        private Color accentBlue = Color.FromArgb(14, 165, 233);
        private Color accentGreen = Color.FromArgb(34, 197, 94);
        private Color accentPurple = Color.FromArgb(99, 102, 241);

        public MainForm()
        {
            cloudflaredExe = Path.Combine(projectDir, "cloudflared.exe");

            InitializeComponent();
            SetupTray();

            this.Load += (s, e) => {
                StartServicesAndTunnel();
            };

            this.FormClosing += (s, e) => {
                KillTunnel();
                if (trayIcon != null)
                {
                    trayIcon.Visible = false;
                    trayIcon.Dispose();
                }
            };
        }

        private void InitializeComponent()
        {
            this.Text = "PeliCle - Group 3 Live Tunnel Hub";
            this.Size = new Size(620, 680);
            this.MinimumSize = new Size(620, 680);
            this.StartPosition = FormStartPosition.CenterScreen;
            this.BackColor = bgDark;
            this.ForeColor = textPrimary;
            this.Font = new Font("Segoe UI", 9.5f, FontStyle.Regular);

            string iconPath = Path.Combine(projectDir, @"public\favicon.ico");
            if (File.Exists(iconPath))
            {
                try { this.Icon = new Icon(iconPath); } catch {}
            }

            // Top Header Panel
            Panel headerPanel = new Panel();
            headerPanel.Dock = DockStyle.Top;
            headerPanel.Height = 70;
            headerPanel.Padding = new Padding(20, 14, 20, 0);

            lblHeaderTitle = new Label();
            lblHeaderTitle.Text = "🚗 PeliCle Live Tunnel Hub";
            lblHeaderTitle.Font = new Font("Segoe UI", 16f, FontStyle.Bold);
            lblHeaderTitle.ForeColor = accentBlue;
            lblHeaderTitle.AutoSize = true;
            lblHeaderTitle.Location = new Point(18, 12);

            lblHeaderSubtitle = new Label();
            lblHeaderSubtitle.Text = "CSU Lal-lo Campus Vehicle & Trip Management | Group 3";
            lblHeaderSubtitle.Font = new Font("Segoe UI", 9f, FontStyle.Regular);
            lblHeaderSubtitle.ForeColor = textSecondary;
            lblHeaderSubtitle.AutoSize = true;
            lblHeaderSubtitle.Location = new Point(22, 42);

            headerPanel.Controls.Add(lblHeaderTitle);
            headerPanel.Controls.Add(lblHeaderSubtitle);
            this.Controls.Add(headerPanel);

            // Main Layout Container
            Panel mainPanel = new Panel();
            mainPanel.Dock = DockStyle.Fill;
            mainPanel.Padding = new Padding(20, 0, 20, 16);
            mainPanel.AutoScroll = true;

            int currentY = 5;

            // 1. Status Badges Panel
            Panel statusPanel = CreateCardPanel(currentY, 44);
            lblStatusMysql = CreateStatusLabel("● MySQL: Checking...", 14, 12);
            lblStatusHerd = CreateStatusLabel("● Herd: Active", 190, 12);
            lblStatusTunnel = CreateStatusLabel("● Tunnel: Starting...", 360, 12);
            statusPanel.Controls.Add(lblStatusMysql);
            statusPanel.Controls.Add(lblStatusHerd);
            statusPanel.Controls.Add(lblStatusTunnel);
            mainPanel.Controls.Add(statusPanel);
            currentY += 54;

            // 2. Primary Tunnel Link Card
            Panel tunnelCard = CreateCardPanel(currentY, 130);
            Label lblTunnelTitle = new Label();
            lblTunnelTitle.Text = "🌐 PUBLIC LIVE TUNNEL LINK";
            lblTunnelTitle.Font = new Font("Segoe UI", 9f, FontStyle.Bold);
            lblTunnelTitle.ForeColor = accentGreen;
            lblTunnelTitle.Location = new Point(16, 12);
            lblTunnelTitle.AutoSize = true;

            txtUrl = new TextBox();
            txtUrl.Text = "Generating secure tunnel link, please wait...";
            txtUrl.Location = new Point(16, 36);
            txtUrl.Width = 526;
            txtUrl.Font = new Font("Consolas", 10.5f, FontStyle.Bold);
            txtUrl.BackColor = Color.FromArgb(15, 23, 42);
            txtUrl.ForeColor = Color.FromArgb(56, 189, 248);
            txtUrl.BorderStyle = BorderStyle.FixedSingle;
            txtUrl.ReadOnly = true;

            btnCopyUrl = CreateButton("📋 Copy Link", 16, 76, 160, 36, accentBlue);
            btnCopyUrl.Click += (s, e) => {
                if (!string.IsNullOrEmpty(currentUrl))
                {
                    Clipboard.SetText(currentUrl);
                    btnCopyUrl.Text = "✓ Copied!";
                    var t = new System.Windows.Forms.Timer { Interval = 1800 };
                    t.Tick += (ts, te) => { btnCopyUrl.Text = "📋 Copy Link"; t.Stop(); t.Dispose(); };
                    t.Start();
                }
            };

            btnOpenUrl = CreateButton("🚀 Open Website", 186, 76, 160, 36, Color.FromArgb(16, 185, 129));
            btnOpenUrl.Click += (s, e) => {
                if (!string.IsNullOrEmpty(currentUrl))
                    Process.Start(currentUrl);
            };

            btnRestartTunnel = CreateButton("🔄 New Link", 356, 76, 186, 36, Color.FromArgb(100, 116, 139));
            btnRestartTunnel.Click += (s, e) => {
                StartServicesAndTunnel();
            };

            tunnelCard.Controls.Add(lblTunnelTitle);
            tunnelCard.Controls.Add(txtUrl);
            tunnelCard.Controls.Add(btnCopyUrl);
            tunnelCard.Controls.Add(btnOpenUrl);
            tunnelCard.Controls.Add(btnRestartTunnel);
            mainPanel.Controls.Add(tunnelCard);
            currentY += 140;

            // 3. Quick Portals Card
            Panel portalsCard = CreateCardPanel(currentY, 135);
            Label lblPortals = new Label();
            lblPortals.Text = "⚡ QUICK ACCESS PORTALS";
            lblPortals.Font = new Font("Segoe UI", 9f, FontStyle.Bold);
            lblPortals.ForeColor = textSecondary;
            lblPortals.Location = new Point(16, 10);
            lblPortals.AutoSize = true;
            portalsCard.Controls.Add(lblPortals);

            // Row 1: Employee
            Label lblEmp = new Label { Text = "👤 Employee Portal", Location = new Point(16, 36), AutoSize = true, Font = new Font("Segoe UI", 9.5f, FontStyle.Bold) };
            btnOpenEmployee = CreateSmallButton("Open", 380, 32, 70, 26, accentPurple);
            btnCopyEmployee = CreateSmallButton("Copy", 458, 32, 70, 26, cardBorder);
            btnOpenEmployee.Click += (s, e) => { if (!string.IsNullOrEmpty(currentUrl)) Process.Start(currentUrl + "/employee"); };
            btnCopyEmployee.Click += (s, e) => { if (!string.IsNullOrEmpty(currentUrl)) { Clipboard.SetText(currentUrl + "/employee"); ShowToast("Employee link copied!"); } };
            portalsCard.Controls.Add(lblEmp);
            portalsCard.Controls.Add(btnOpenEmployee);
            portalsCard.Controls.Add(btnCopyEmployee);

            // Row 2: Admin
            Label lblAdm = new Label { Text = "🛡️ Admin Portal", Location = new Point(16, 68), AutoSize = true, Font = new Font("Segoe UI", 9.5f, FontStyle.Bold) };
            btnOpenAdmin = CreateSmallButton("Open", 380, 64, 70, 26, accentPurple);
            btnCopyAdmin = CreateSmallButton("Copy", 458, 64, 70, 26, cardBorder);
            btnOpenAdmin.Click += (s, e) => { if (!string.IsNullOrEmpty(currentUrl)) Process.Start(currentUrl + "/admin"); };
            btnCopyAdmin.Click += (s, e) => { if (!string.IsNullOrEmpty(currentUrl)) { Clipboard.SetText(currentUrl + "/admin"); ShowToast("Admin link copied!"); } };
            portalsCard.Controls.Add(lblAdm);
            portalsCard.Controls.Add(btnOpenAdmin);
            portalsCard.Controls.Add(btnCopyAdmin);

            // Row 3: Guard Scanner
            Label lblGrd = new Label { Text = "📷 Guard QR Scanner", Location = new Point(16, 100), AutoSize = true, Font = new Font("Segoe UI", 9.5f, FontStyle.Bold) };
            btnOpenGuard = CreateSmallButton("Open", 380, 96, 70, 26, accentPurple);
            btnCopyGuard = CreateSmallButton("Copy", 458, 96, 70, 26, cardBorder);
            btnOpenGuard.Click += (s, e) => { if (!string.IsNullOrEmpty(currentUrl)) Process.Start(currentUrl + "/guard/scanner"); };
            btnCopyGuard.Click += (s, e) => { if (!string.IsNullOrEmpty(currentUrl)) { Clipboard.SetText(currentUrl + "/guard/scanner"); ShowToast("Guard Scanner link copied!"); } };
            portalsCard.Controls.Add(lblGrd);
            portalsCard.Controls.Add(btnOpenGuard);
            portalsCard.Controls.Add(btnCopyGuard);

            mainPanel.Controls.Add(portalsCard);
            currentY += 145;

            // 4. Live Sync Guarantee Notice Card
            Panel noticeCard = CreateCardPanel(currentY, 68);
            noticeCard.BackColor = Color.FromArgb(20, 30, 48);
            Label lblNoticeTitle = new Label {
                Text = "🔥 LIVE SYNC ACTIVE: Kahit may bagong code, OK pa rin ang link!",
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                ForeColor = Color.FromArgb(250, 204, 21), // Amber 400
                Location = new Point(14, 10),
                AutoSize = true
            };
            Label lblNoticeDesc = new Label {
                Text = "Kahit mag-save ka ng code sa VS Code (Ctrl+S), hindi mo kailangang i-restart ang link.\nI-refresh (F5) lang ang phone o browser, live na agad ang mga pagbabago mo!",
                Font = new Font("Segoe UI", 8.5f, FontStyle.Regular),
                ForeColor = textSecondary,
                Location = new Point(14, 30),
                AutoSize = true
            };
            noticeCard.Controls.Add(lblNoticeTitle);
            noticeCard.Controls.Add(lblNoticeDesc);
            mainPanel.Controls.Add(noticeCard);
            currentY += 76;

            // 5. Live Logs / Connection Feed Card
            Panel logCard = CreateCardPanel(currentY, 140);
            Label lblLogTitle = new Label {
                Text = "📡 LIVE CONNECTION ACTIVITY",
                Font = new Font("Segoe UI", 8.5f, FontStyle.Bold),
                ForeColor = textSecondary,
                Location = new Point(14, 8),
                AutoSize = true
            };
            rtbLogs = new RichTextBox {
                Location = new Point(14, 28),
                Width = 528,
                Height = 100,
                BackColor = Color.FromArgb(10, 15, 28),
                ForeColor = Color.FromArgb(148, 163, 184),
                Font = new Font("Consolas", 8.5f),
                BorderStyle = BorderStyle.None,
                ReadOnly = true,
                ScrollBars = RichTextBoxScrollBars.Vertical
            };
            logCard.Controls.Add(lblLogTitle);
            logCard.Controls.Add(rtbLogs);
            mainPanel.Controls.Add(logCard);

            this.Controls.Add(mainPanel);
        }

        private void SetupTray()
        {
            trayMenu = new ContextMenuStrip();
            trayMenu.Items.Add("Open Hub", null, (s, e) => {
                this.Show();
                this.WindowState = FormWindowState.Normal;
                this.BringToFront();
            });
            trayMenu.Items.Add("Copy Current URL", null, (s, e) => {
                if (!string.IsNullOrEmpty(currentUrl)) Clipboard.SetText(currentUrl);
            });
            trayMenu.Items.Add("-");
            trayMenu.Items.Add("Exit", null, (s, e) => {
                this.Close();
            });

            trayIcon = new NotifyIcon {
                Text = "PeliCle Group 3 Tunnel Hub",
                ContextMenuStrip = trayMenu,
                Visible = true
            };

            string iconPath = Path.Combine(projectDir, @"public\favicon.ico");
            if (File.Exists(iconPath))
            {
                try { trayIcon.Icon = new Icon(iconPath); } catch {}
            }
            else
            {
                trayIcon.Icon = SystemIcons.Application;
            }

            trayIcon.DoubleClick += (s, e) => {
                this.Show();
                this.WindowState = FormWindowState.Normal;
                this.BringToFront();
            };
        }

        private void ShowToast(string message)
        {
            if (trayIcon != null)
            {
                trayIcon.ShowBalloonTip(2000, "PeliCle Live Tunnel", message, ToolTipIcon.Info);
            }
        }

        private void AppendLog(string message, Color color)
        {
            if (rtbLogs.InvokeRequired)
            {
                rtbLogs.Invoke(new Action(() => AppendLog(message, color)));
                return;
            }

            rtbLogs.SelectionStart = rtbLogs.TextLength;
            rtbLogs.SelectionLength = 0;
            rtbLogs.SelectionColor = color;
            rtbLogs.AppendText(string.Format("[{0}] {1}\r\n", DateTime.Now.ToString("HH:mm:ss"), message));
            rtbLogs.SelectionColor = rtbLogs.ForeColor;
            rtbLogs.ScrollToCaret();
        }

        private void StartServicesAndTunnel()
        {
            AppendLog("Initializing PeliCle services...", Color.Cyan);
            txtUrl.Text = "Connecting to MySQL and starting tunnel...";
            lblStatusTunnel.Text = "● Tunnel: Starting...";
            lblStatusTunnel.ForeColor = Color.Gold;

            ThreadPool.QueueUserWorkItem(state => {
                // 1. Ensure MySQL is running
                try
                {
                    Process[] mysqlds = Process.GetProcessesByName("mysqld");
                    if (mysqlds.Length == 0)
                    {
                        AppendLog("Starting MySQL server...", Color.Yellow);
                        string mysqlExe = @"C:\xampp\mysql\bin\mysqld.exe";
                        if (File.Exists(mysqlExe))
                        {
                            ProcessStartInfo psiMysql = new ProcessStartInfo {
                                FileName = mysqlExe,
                                Arguments = @"--defaults-file=""C:\xampp\mysql\bin\my.ini"" --standalone",
                                WindowStyle = ProcessWindowStyle.Hidden,
                                CreateNoWindow = true,
                                UseShellExecute = false
                            };
                            Process.Start(psiMysql);
                            Thread.Sleep(1500);
                        }
                    }
                    this.Invoke(new Action(() => {
                        lblStatusMysql.Text = "● MySQL: Running";
                        lblStatusMysql.ForeColor = accentGreen;
                    }));
                }
                catch (Exception ex)
                {
                    AppendLog("MySQL Check note: " + ex.Message, Color.Orange);
                }

                // 2. Kill old tunnel if any
                KillTunnel();

                // 3. Launch cloudflared
                try
                {
                    if (!File.Exists(cloudflaredExe))
                    {
                        string herdBin = @"C:\Users\Acer\.config\herd\bin\cloudflared.exe";
                        if (File.Exists(herdBin)) cloudflaredExe = herdBin;
                    }

                    AppendLog("Starting Cloudflare Tunnel to Herd (group-3.test)...", Color.Cyan);

                    ProcessStartInfo psi = new ProcessStartInfo {
                        FileName = cloudflaredExe,
                        Arguments = "tunnel --url https://127.0.0.1:443 --no-tls-verify --http-host-header group-3.test",
                        RedirectStandardError = true,
                        RedirectStandardOutput = true,
                        UseShellExecute = false,
                        CreateNoWindow = true
                    };

                    tunnelProcess = new Process { StartInfo = psi, EnableRaisingEvents = true };

                    tunnelProcess.OutputDataReceived += (s, e) => {
                        if (!string.IsNullOrEmpty(e.Data)) ParseTunnelOutput(e.Data);
                    };

                    tunnelProcess.ErrorDataReceived += (s, e) => {
                        if (!string.IsNullOrEmpty(e.Data)) ParseTunnelOutput(e.Data);
                    };

                    tunnelProcess.Start();
                    tunnelProcess.BeginOutputReadLine();
                    tunnelProcess.BeginErrorReadLine();
                }
                catch (Exception ex)
                {
                    AppendLog("Failed to start tunnel: " + ex.Message, Color.Red);
                    this.Invoke(new Action(() => {
                        lblStatusTunnel.Text = "● Tunnel: Error";
                        lblStatusTunnel.ForeColor = Color.Red;
                        txtUrl.Text = "Error starting cloudflared: " + ex.Message;
                    }));
                }
            });
        }

        private void ParseTunnelOutput(string line)
        {
            // Scan for trycloudflare URL
            Match m = Regex.Match(line, @"https://[a-zA-Z0-9-]+\.trycloudflare\.com");
            if (m.Success)
            {
                string foundUrl = m.Value;
                if (foundUrl != currentUrl)
                {
                    currentUrl = foundUrl;
                    this.Invoke(new Action(() => {
                        txtUrl.Text = currentUrl;
                        lblStatusTunnel.Text = "● Tunnel: Online";
                        lblStatusTunnel.ForeColor = accentGreen;
                        AppendLog("LIVE TUNNEL CREATED: " + currentUrl, Color.LimeGreen);

                        try { Clipboard.SetText(currentUrl); } catch {}
                        ShowToast("Live Tunnel Ready!\n" + currentUrl);

                        // Save text file & shortcut on desktop
                        try
                        {
                            string desktop = Environment.GetFolderPath(Environment.SpecialFolder.Desktop);
                            string txtPath = Path.Combine(desktop, "TUNNEL_LINK_GROUP_3.txt");
                            string content = string.Format(
                                "========================================================================\r\n" +
                                "                GROUP 3 LIVE TUNNEL (PELICLE)\r\n" +
                                "========================================================================\r\n\r\n" +
                                "LIVE URL:       {0}\r\n" +
                                "EMPLOYEE:       {0}/employee\r\n" +
                                "ADMIN:          {0}/admin\r\n" +
                                "GUARD SCANNER:  {0}/guard/scanner\r\n\r\n" +
                                "PAALALA:\r\n" +
                                "- Kahit baguhin o i-save ang code sa VS Code, HINDI MAGBABAGO ANG LINK!\r\n" +
                                "- I-refresh (F5) lang sa browser o phone para makita ang bagong code.\r\n" +
                                "========================================================================",
                                currentUrl
                            );
                            File.WriteAllText(txtPath, content);

                            string urlPath = Path.Combine(desktop, "Group 3 Live Tunnel.url");
                            File.WriteAllText(urlPath, "[InternetShortcut]\r\nURL=" + currentUrl + "\r\n");
                        }
                        catch {}
                    }));
                }
            }

            // Also log useful traffic events
            if (line.Contains("GET") || line.Contains("POST") || line.Contains("HTTP"))
            {
                AppendLog(line, Color.LightGray);
            }
        }

        private void KillTunnel()
        {
            try
            {
                if (tunnelProcess != null && !tunnelProcess.HasExited)
                {
                    tunnelProcess.Kill();
                    tunnelProcess.Dispose();
                    tunnelProcess = null;
                }
            }
            catch {}

            try
            {
                foreach (var p in Process.GetProcessesByName("cloudflared"))
                {
                    try { p.Kill(); } catch {}
                }
            }
            catch {}
        }

        // Helper UI Creators
        private Panel CreateCardPanel(int y, int height)
        {
            Panel p = new Panel();
            p.Location = new Point(20, y);
            p.Width = 560;
            p.Height = height;
            p.BackColor = cardBg;
            p.BorderStyle = BorderStyle.None;
            p.Paint += (s, e) => {
                using (Pen pen = new Pen(cardBorder, 1))
                {
                    e.Graphics.DrawRectangle(pen, 0, 0, p.Width - 1, p.Height - 1);
                }
            };
            return p;
        }

        private Label CreateStatusLabel(string text, int x, int y)
        {
            Label lbl = new Label();
            lbl.Text = text;
            lbl.Location = new Point(x, y);
            lbl.AutoSize = true;
            lbl.Font = new Font("Segoe UI", 9f, FontStyle.Bold);
            lbl.ForeColor = textSecondary;
            return lbl;
        }

        private Button CreateButton(string text, int x, int y, int w, int h, Color bg)
        {
            Button btn = new Button();
            btn.Text = text;
            btn.Location = new Point(x, y);
            btn.Size = new Size(w, h);
            btn.BackColor = bg;
            btn.ForeColor = Color.White;
            btn.FlatStyle = FlatStyle.Flat;
            btn.FlatAppearance.BorderSize = 0;
            btn.Font = new Font("Segoe UI", 9.5f, FontStyle.Bold);
            btn.Cursor = Cursors.Hand;
            return btn;
        }

        private Button CreateSmallButton(string text, int x, int y, int w, int h, Color bg)
        {
            Button btn = new Button();
            btn.Text = text;
            btn.Location = new Point(x, y);
            btn.Size = new Size(w, h);
            btn.BackColor = bg;
            btn.ForeColor = Color.White;
            btn.FlatStyle = FlatStyle.Flat;
            btn.FlatAppearance.BorderSize = 0;
            btn.Font = new Font("Segoe UI", 8.5f, FontStyle.Bold);
            btn.Cursor = Cursors.Hand;
            return btn;
        }
    }
}
