<?php
session_start();

$configs = include("config/config.php");
$mysqlServer = $configs["mysqlServer"];
$mysqlDb = $configs["mysqlDb"];
$mysqlUsername = $configs["mysqlUsername"];
$mysqlPassword = $configs["mysqlPassword"];

if (isset($_POST["register"])) {
	if (isset($_POST["registerEmailAddress"]) && isset($_POST["registerPassword"]) && strlen($_POST["registerPassword"]) >= 8) {
		$email = test_string($_POST["registerEmailAddress"]);
		$password = test_string($_POST["registerPassword"]);
		
		$salt = bin2hex(random_bytes(32));
		$hash = hash_password($password, $salt);
		
		try {
			$conn = new PDO("mysql:host=$mysqlServer;dbname=$mysqlDb", $mysqlUsername, $mysqlPassword);
			$result = $conn->exec("INSERT INTO Accounts (Email, Password, Salt) VALUES ('$email', '$hash', '$salt')");
			$_SESSION["loggedIn"] = true;
			$_SESSION["email"] = $email;
			$conn = null;
		} catch (PDOException $e) {
			$registerError = "Account already exists. Please login to your account.";
		}
	}
} else if (isset($_POST["login"])) {
	if (isset($_POST["loginEmailAddress"]) && isset($_POST["loginPassword"])) {
		$email = test_string($_POST["loginEmailAddress"]);
		$password = test_string($_POST["loginPassword"]);
		
		try {
			$conn = new PDO("mysql:host=$mysqlServer;dbname=$mysqlDb", $mysqlUsername, $mysqlPassword);
			$stmt = $conn->prepare("SELECT Password, Salt FROM Accounts WHERE Email='$email'");
			$stmt->execute();
			$row = $stmt->fetch();
		} catch (PDOException $e) {
			$loginError = "Database access error";
		}
	
		$hashTry = $hashActual = "";
		if ($row !== false && $stmt->rowCount() === 1) {
			$hashActual = $row["Password"];
			$salt = $row["Salt"];
			$hashTry = hash_password($password, $salt);
		}
		$conn = null;
		if ($hashTry != "" && $hashActual != "" && $hashTry === $hashActual) {
			$_SESSION["loggedIn"] = true;
			$_SESSION["email"] = $email;
		} else {
			$loginError = "Incorrect email or password. Please try again.";
		}
	}
} else if (isset($_POST["logout"])) {
	session_unset();
	session_destroy();
} else if (isset($_POST["saveCompounding"])) {
	if (isset($_POST["currentAge"]) && isset($_POST["targetRetirementAge"]) && isset($_POST["beginningBalance"]) && isset($_POST["annualSavings"]) && isset($_POST["annualSavingsIncreaseRate"]) && isset($_POST["expectedAnnualReturn"])) {
		$currentAge = test_number($_POST["currentAge"]);
		$targetRetirementAge = test_number($_POST["targetRetirementAge"]);
		$beginningBalance = test_number($_POST["beginningBalance"]);
		$annualSavings = test_number($_POST["annualSavings"]);
		$annualSavingsIncreaseRate = test_number($_POST["annualSavingsIncreaseRate"]);
		$expectedAnnualReturn = test_number($_POST["expectedAnnualReturn"]);
		
		try {
			$conn = new PDO("mysql:host=$mysqlServer;dbname=$mysqlDb", $mysqlUsername, $mysqlPassword);
			$conn->exec("UPDATE Accounts SET CurrentAge=$currentAge, TargetRetirementAge=$targetRetirementAge, BeginningBalance=$beginningBalance, AnnualSavings=$annualSavings, AnnualSavingsIncreaseRate=$annualSavingsIncreaseRate, ExpectedAnnualReturn=$expectedAnnualReturn WHERE Email='" . $_SESSION['email'] . "'");
			$conn = null;
		} catch (PDOException $e) {
			$loginError = "Database access error";
		}
	}
} else if (isset($_POST["saveSpending"])) {
	if (isset($_POST["annualIncome"]) && isset($_POST["monthlyEssentialExpenses"]) && isset($_POST["emergencyFund"]) && isset($_POST["debt"]) && isset($_POST["contributionsThisYear"]) && isset($_POST["company401kMatch"]) && isset($_POST["iraContributionsThisYear"])) {
		$age50OrOlder = isset($_POST["age50OrOlder"]) ? 1 : 0;
		$annualIncome = test_number($_POST["annualIncome"]);
		$monthlyEssentialExpenses = test_number($_POST["monthlyEssentialExpenses"]);
		$emergencyFund = test_number($_POST["emergencyFund"]);
		$debt = test_number($_POST["debt"]);
		$contributionsThisYear = test_number($_POST["contributionsThisYear"]);
		$company401kMatch = test_number($_POST["company401kMatch"]);
		$iraContributionsThisYear = test_number($_POST["iraContributionsThisYear"]);
		
		try {
			$conn = new PDO("mysql:host=$mysqlServer;dbname=$mysqlDb", $mysqlUsername, $mysqlPassword);
			$conn->exec("UPDATE Accounts SET Age50OrOlder=$age50OrOlder, AnnualIncome=$annualIncome, MonthlyEssentialExpenses=$monthlyEssentialExpenses, EmergencyFund=$emergencyFund, Debt=$debt, ContributionsThisYear=$contributionsThisYear, Company401kMatch=$company401kMatch, IRAContributionsThisYear=$iraContributionsThisYear WHERE Email='" . $_SESSION['email'] . "'");
			$conn = null;
		} catch (PDOException $e) {
			$loginError = "Database access error";
		}
	}
} else if (isset($_POST["saveSaving"])) {
	if (isset($_POST["initialSavings"]) && isset($_POST["annualReturns"]) && isset($_POST["withdrawalRate"]) && isset($_POST["expensesInRetirement"])) {
		$initialSavings = test_number($_POST["initialSavings"]);
		$frontLoadAnnualSavings = isset($_POST["frontLoadAnnualSavings"]) ? 1 : 0;
		$annualReturns = test_number($_POST["annualReturns"]);
		$withdrawalRate = test_number($_POST["withdrawalRate"]);
		$expensesInRetirement = test_number($_POST["expensesInRetirement"]);
		
		try {
			$conn = new PDO("mysql:host=$mysqlServer;dbname=$mysqlDb", $mysqlUsername, $mysqlPassword);
			$conn->exec("UPDATE Accounts SET InitialSavings=$initialSavings, FrontLoadAnnualSavings=$frontLoadAnnualSavings, AnnualReturns=$annualReturns, WithdrawalRate=$withdrawalRate, ExpensesInRetirement=$expensesInRetirement WHERE Email='" . $_SESSION['email'] . "'");
			$conn = null;
		} catch (PDOException $e) {
			$loginError = "Database access error";
		}
	}
}

if (isset($_SESSION["loggedIn"])) {
	try {
		$conn = new PDO("mysql:host=$mysqlServer;dbname=$mysqlDb", $mysqlUsername, $mysqlPassword);
		$stmt = $conn->prepare("SELECT CurrentAge, TargetRetirementAge, BeginningBalance, AnnualSavings, AnnualSavingsIncreaseRate, ExpectedAnnualReturn, Age50OrOlder, AnnualIncome, MonthlyEssentialExpenses, EmergencyFund, Debt, ContributionsThisYear, Company401kMatch, IRAContributionsThisYear, InitialSavings, FrontLoadAnnualSavings, AnnualReturns, WithdrawalRate, ExpensesInRetirement FROM Accounts WHERE Email='" . $_SESSION['email'] . "'");
		$stmt->execute();
		$row = $stmt->fetch();
	} catch (PDOException $e) {
		$loginError = "Database access error";
	}
	if ($row["CurrentAge"] != null && $row["TargetRetirementAge"] != null && $row["BeginningBalance"] != null && $row["AnnualSavings"] != null && $row["AnnualSavingsIncreaseRate"] != null && $row["ExpectedAnnualReturn"] != null) {
		$_SESSION["currentAge"] = $row["CurrentAge"];
		$_SESSION["targetRetirementAge"] = $row["TargetRetirementAge"];
		$_SESSION["beginningBalance"] = $row["BeginningBalance"];
		$_SESSION["annualSavings"] = $row["AnnualSavings"];
		$_SESSION["annualSavingsIncreaseRate"] = $row["AnnualSavingsIncreaseRate"];
		$_SESSION["expectedAnnualReturn"] = $row["ExpectedAnnualReturn"];
	}
	if ($row["Age50OrOlder"] != null && $row["AnnualIncome"] != null && $row["MonthlyEssentialExpenses"] != null && $row["EmergencyFund"] != null && $row["Debt"] != null && $row["ContributionsThisYear"] != null && $row["Company401kMatch"] != null && $row["IRAContributionsThisYear"] != null) {
		$_SESSION["age50OrOlder"] = $row["Age50OrOlder"];
		$_SESSION["annualIncome"] = $row["AnnualIncome"];
		$_SESSION["monthlyEssentialExpenses"] = $row["MonthlyEssentialExpenses"];
		$_SESSION["emergencyFund"] = $row["EmergencyFund"];
		$_SESSION["debt"] = $row["Debt"];
		$_SESSION["contributionsThisYear"] = $row["ContributionsThisYear"];
		$_SESSION["company401kMatch"] = $row["Company401kMatch"];
		$_SESSION["iraContributionsThisYear"] = $row["IRAContributionsThisYear"];
	}
	if ($row["InitialSavings"] != null && $row["FrontLoadAnnualSavings"] != null && $row["AnnualReturns"] != null && $row["WithdrawalRate"] != null && $row["ExpensesInRetirement"] != null) {
		$_SESSION["initialSavings"] = $row["InitialSavings"];
		$_SESSION["frontLoadAnnualSavings"] = $row["FrontLoadAnnualSavings"];
		$_SESSION["annualReturns"] = $row["AnnualReturns"];
		$_SESSION["withdrawalRate"] = $row["WithdrawalRate"];
		$_SESSION["expensesInRetirement"] = $row["ExpensesInRetirement"];
	}
	$conn = null;
}

function test_string($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

function test_number($data) {
	$data = test_string($data);
	return $data === "" ? 0 : $data;
}

function hash_password($password, $salt) {
	return hash_pbkdf2("sha256", $password, $salt, 10000);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="Personalized tools to help reach your financial goals. Optimize your saving, spending, and investing.">
	<link rel="icon" type="image/png" href="bamboo.png" />
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/rangeslider.js/2.3.3/rangeslider.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.4/angular.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.bundle.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/rangeslider.js/2.3.3/rangeslider.min.js"></script>
	<title>Bamboo - Personal Finance Utility</title>
	<style>
	body { overflow-y: scroll; }
	.nav-pills > li.active > a, .nav-pills > li.active > a:hover, .nav-pills > li.active > a:focus { background-color: #99bc20; }
	.material-icons.md-16 { font-size: 16px; }
	.switch { position: relative; display: inline-block; width: 50px; height: 26px; }
	.switch input { opacity: 0; width: 0; height: 0; }
	.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; -webkit-transition: .4s; transition: .4s; }
	.slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 4px; background-color: white; -webkit-transition: .4s; transition: .4s; }
	input:checked + .slider { background-color: #2196F3; }
	input:focus + .slider { box-shadow: 0 0 1px #2196F3; }
	input:checked + .slider:before { -webkit-transform: translateX(24px); -ms-transform: translateX(24px); transform: translateX(24px); }
	.slider.round { border-radius: 26px; }
	.slider.round:before { border-radius: 50%; }
	.rangeslider__fill { background: #99bc20; }
	</style>
</head>
<body>

<div class="container-fluid" ng-app="myApp" ng-controller="myCtrl">
	<div class="row">
		<div class="page-header">
			<?php
			echo isset($_SESSION['loggedIn']) ?
			'<form method="post" action="/">
				<button type="submit" name="logout" class="btn btn-success pull-right" style="margin-right:30px;">Logout</button>
			</form>
			<h5 class="pull-right" style="margin-right:20px;">' . $_SESSION["email"] . '</h5>' :
			'<button type="button" class="btn btn-success pull-right" data-toggle="modal" data-target="#register" style="margin-right:30px;">Register</button>
			<button type="button" class="btn btn-success pull-right" data-toggle="modal" data-target="#login" style="margin-right:20px;">Login</button>';
			?>
			<h1 style="margin-left:30px;"><a href="." style="text-decoration:none;"><strong style="color:#99bc20;">Bamboo</strong><img src="bamboo.png" height="33px" style="margin-left:5px;"></a></h1>
			<h4 style="margin-left:30px;"><small>Grow your savings for financial independence or retirement</small></h4>
			<div id="register" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Register</h4>
						</div>
						<div class="modal-body">
							<form id="formRegister" method="post" action="/">
								<div class="form-group">
									<label for="registerEmailAddress">Email Address:</label>
									<input id="registerEmailAddress" type="email" class="form-control" name="registerEmailAddress" required>
								</div>
								<div class="form-group">
									<label for="registerPassword">Password: (8 characters minimum)</label>
									<input id="registerPassword" type="password" class="form-control" name="registerPassword" pattern=".{8,}" title="8 characters minimum" required>
								</div>
							</form>
						</div>
						<div class="modal-footer">
							<button type="submit" name="register" class="btn btn-success pull-right" form="formRegister">Register</button>
						</div>
					</div>
				</div>
			</div>
			<div id="login" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Login</h4>
						</div>
						<div class="modal-body">
							<form id="formLogin" method="post" action="/">
								<?php
									if (isset($registerError)) {
										echo "<div class='alert alert-danger'>$registerError</div>";
									} else if (isset($loginError)) {
										echo "<div class='alert alert-danger'>$loginError</div>";
									}
								?>
								<div class="form-group">
									<label for="loginEmailAddress">Email Address:</label>
									<input id="loginEmailAddress" type="email" class="form-control" name="loginEmailAddress" required>
								</div>
								<div class="form-group">
									<label for="loginPassword">Password:</label>
									<input id="loginPassword" type="password" class="form-control" name="loginPassword" required>
								</div>
							</form>
						</div>
						<div class="modal-footer">
							<button type="submit" name="login" class="btn btn-success pull-right" form="formLogin">Login</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-2">
			<p style="color:#99bc20; font-size:1.25em; border-bottom: 1px solid; margin-bottom:5px;">Tools</p>
			<ul class="nav nav-pills nav-stacked">
				<li id="compoundingPill" <?php if (!isset($_POST["saveSpending"]) && !isset($_POST["saveSaving"])) {echo 'class="active"';}?>><a data-toggle="pill" href="#compounding">Compounding</a></li>
				<li id="spendingPill" <?php if (isset($_POST["saveSpending"])) {echo 'class="active"';}?>><a data-toggle="pill" href="#spending">Spending Prioritization</a></li>
				<li id="savingPill" <?php if (isset($_POST["saveSaving"])) {echo 'class="active"';}?>><a data-toggle="pill" href="#saving">Years to Retirement</a></li>
			</ul>
			<br>
		</div>
		<div class="col-md-9 tab-content">
			<div id="compounding" class="tab-pane fade <?php if (!isset($_POST["saveSpending"]) && !isset($_POST["saveSaving"])) {echo "in active";}?>" ng-init="updateCompounding()">
				<div class="row">
					<div class="col-md-1"></div>
					<div class="col-md-10">
						<div class="alert alert-success" style="font-size:18px;">
							Over the years, the power of compound interest can turn your savings and investments into a sizable nest egg.
							The length of time that you stay invested is extremely important.
							Contrary to some beliefs, decreasing that period by a year results in the loss of the potential gains in the last compounding year, not the first.
							This takes a big chunk out of the balance because the compounding periods at the end are the most valuable.
							You can test this in the utility below.
						</div>
					</div>
					<div class="col-md-1"></div>
				</div>
				<div class="row">
					<div class="col-md-3">
						<form method="post" action="/">
							<div class="form-group">
								<label for="currentAge">Current Age:</label>
								<div class="input-group">
									<input id="currentAge" type="number" class="form-control" name="currentAge" ng-model="currentAge" ng-change="updateCompounding()" min="0" max="{{ targetRetirementAge }}">
									<span class="input-group-addon">Years</span>
								</div>
							</div>
							<div class="form-group">
								<label for="targetRetirementAge">Target Retirement Age:</label>
								<div class="input-group">
									<input id="targetRetirementAge" type="number" class="form-control" name="targetRetirementAge" ng-model="targetRetirementAge" ng-change="updateCompounding()" min="{{ currentAge }}">
									<span class="input-group-addon">Years</span>
								</div>
							</div>
							<div class="form-group">
								<label for="beginningBalance">Beginning Balance:</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="glyphicon glyphicon-usd"></i></span>
									<input id="beginningBalance" type="number" class="form-control" name="beginningBalance" ng-model="beginningBalance" ng-change="updateCompounding()">
								</div>
							</div>
							<div class="form-group">
								<label for="annualSavings">Annual Savings:</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="glyphicon glyphicon-usd"></i></span>
									<input id="annualSavings" type="number" class="form-control" name="annualSavings" ng-model="annualSavings" ng-change="updateCompounding()">
								</div>
							</div>
							<div class="form-group">
								<label for="annualSavingsIncreaseRate">Annual Savings Increase Rate:</label>
								<a data-toggle="tooltip" title="The percentage increase in your savings amount per year"><span class="glyphicon glyphicon-info-sign"></span></a>
								<div class="input-group">
									<input id="annualSavingsIncreaseRate" type="number" class="form-control" name="annualSavingsIncreaseRate" ng-model="annualSavingsIncreaseRate" ng-change="updateCompounding()">
									<span class="input-group-addon">%</span>
								</div>
							</div>
							<div class="form-group">
								<label for="expectedAnnualReturn">Expected Annual Return:</label>
								<a data-toggle="tooltip" title="This assumes that you invest all your savings. The annualized inflation-adjusted total returns of the S&P 500 since 1926 is about 7%"><span class="glyphicon glyphicon-info-sign"></span></a>
								<div class="input-group">
									<input id="expectedAnnualReturn" type="number" class="form-control" name="expectedAnnualReturn" ng-model="expectedAnnualReturn" ng-change="updateCompounding()">
									<span class="input-group-addon">%</span>
								</div>
							</div>
							<?php
							if (isset($_SESSION['loggedIn'])) {
								echo '<button type="submit" name="saveCompounding" class="btn btn-success pull-right">Save</button><br><br><br>';
							}
							?>
							<div class="alert alert-success">
								Your ending balance at {{ targetRetirementAge }} is <strong>{{ endingBalance }}</strong>.<br>
								The annual interest is <strong>{{ annualInterest }}</strong>.
							</div>
						</form>
					</div>
					<div class="col-md-9">
						<canvas id="myChart"></canvas>
					</div>
				</div>
				<br>
				<div class="row">
					<div class="panel panel-success">
						<div id="toggleDiv" class="panel-heading text-center" data-toggle="collapse" data-target="#table" style="cursor:pointer;">
							<h4 id="toggleText" class="panel-title" style="text-decoration:underline;">Show Calculations</h4>
						</div>
						<div id="table" class="panel-collapse collapse">
							<div class="panel-body">
								<div class="col-md-3"></div>
								<div class="col-md-6">
									<div class="table-responsive">
										<table class="table table-striped table-bordered table-hover table-condensed">
											<tr>
												<th>Age</th>
												<th>Beginning Balance</th>
												<th>Interest</th>
												<th>Savings</th>
												<th>Ending Balance</th>
											</tr>
											<tr ng-repeat="row in tableData">
												<td>{{ row.age }}</td>
												<td>{{ row.beginningBalance }}</td>
												<td>{{ row.interest }}</td>
												<td>{{ row.savings }}</td>
												<td>{{ row.endingBalance }}</td>
											</tr>
										</table>
									</div>
								</div>
								<div class="col-md-3"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="spending" class="tab-pane fade <?php if (isset($_POST["saveSpending"])) {echo "in active";}?>" ng-init="updateSpending()">
				<div class="row">
					<div class="col-md-1"></div>
					<div class="col-md-10">
						<div class="alert alert-info" style="font-size:18px;">
							You can spend your income in a way that benefits you in the long run by focusing on the more important priorities first.
							Those are the ones that would give you the maximum benefits for your money, such as building up an ample emergency fund (6 months of expenses), taking advantage of free money such as a company 401(k) match, minimizing interest payments by eliminating high-interest debt, and contributing to tax-deferred retirement accounts before contributing to taxable ones.
							Based on the flowchart from <a href="https://www.reddit.com/r/personalfinance/comments/4gdlu9/how_to_prioritize_spending_your_money_a_flowchart/" class="alert-link">Reddit</a>.
						</div>
					</div>
					<div class="col-md-1"></div>
				</div>
				<div class="row">
					<div class="col-md-3">
						<form method="post" action="/">
							<div class="form-group">
								<label for="age50OrOlder">Age 50 or Older?</label>
								<div style="margin-bottom:-10px;">
									<label class="switch">
										<input id="age50OrOlder" type="checkbox" class="form-control" name="age50OrOlder" ng-model="age50OrOlder" ng-change="updateSpending()">
										<span class="slider round"></span>
									</label>
								</div>
							</div>
							<div class="form-group">
								<label for="annualIncome">Annual Income:</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="glyphicon glyphicon-usd"></i></span>
									<input id="annualIncome" type="number" class="form-control" name="annualIncome" ng-model="annualIncome" ng-change="updateSpending()" min="0">
								</div>
							</div>
							<div class="form-group">
								<label for="monthlyEssentialExpenses">Monthly Essential Expenses:</label>
								<a data-toggle="tooltip" title="Rent, utilities, food, insurance, minimum payments, etc."><span class="glyphicon glyphicon-info-sign"></span></a>
								<div class="input-group">
									<span class="input-group-addon"><i class="glyphicon glyphicon-usd"></i></span>
									<input id="monthlyEssentialExpenses" type="number" class="form-control" name="monthlyEssentialExpenses" ng-model="monthlyEssentialExpenses" ng-change="updateSpending()" min="0">
								</div>
							</div>
							<div class="form-group">
								<label for="emergencyFund">Emergency Fund:</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="glyphicon glyphicon-usd"></i></span>
									<input id="emergencyFund" type="number" class="form-control" name="emergencyFund" ng-model="emergencyFund" ng-change="updateSpending()" min="0">
								</div>
							</div>
							<div class="form-group">
								<label for="debt">Debt:</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="glyphicon glyphicon-usd"></i></span>
									<input id="debt" type="number" class="form-control" name="debt" ng-model="debt" ng-change="updateSpending()" min="0">
								</div>
							</div>
							<div class="form-group">
								<label for="contributionsThisYear">401(k) Contributions This Year:</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="glyphicon glyphicon-usd"></i></span>
									<input id="contributionsThisYear" type="number" class="form-control" name="contributionsThisYear" ng-model="contributionsThisYear" ng-change="updateSpending()" min="0">
								</div>
							</div>
							<div class="form-group">
								<label for="company401kMatch">Company 401(k) % Match:</label>
								<a data-toggle="tooltip" title="The percentage of gross income that the employer matches up to. Enter 0 if your company does not match 401(k) contributions"><span class="glyphicon glyphicon-info-sign"></span></a>
								<div class="input-group">
									<input id="company401kMatch" type="number" class="form-control" name="company401kMatch" ng-model="company401kMatch" ng-change="updateSpending()" min="0">
									<span class="input-group-addon">%</span>
								</div>
							</div>
							<div class="form-group">
								<label for="iraContributionsThisYear">IRA Contributions This Year:</label>
								<a data-toggle="tooltip" title="Roth and Traditional combined"><span class="glyphicon glyphicon-info-sign"></span></a>
								<div class="input-group">
									<span class="input-group-addon"><i class="glyphicon glyphicon-usd"></i></span>
									<input id="iraContributionsThisYear" type="number" class="form-control" name="iraContributionsThisYear" ng-model="iraContributionsThisYear" ng-change="updateSpending()" min="0">
								</div>
							</div>
							<?php
							if (isset($_SESSION['loggedIn'])) {
								echo '<button type="submit" name="saveSpending" class="btn btn-success pull-right">Save</button><br><br><br>';
							}
							?>
						</form>
					</div>
					<div class="col-md-9">
						<div id="essentialExpenses" class="alert alert-success">
							Pay <strong>essential expenses</strong> and try to <strong>reduce</strong> them.
						</div>
						<div id="emergencyFundContributions" class="alert alert-info">
							<i class="material-icons md-16">subdirectory_arrow_right</i>
							Contribute <strong>{{ emergencyFundContributions }}</strong> to your <strong>emergency fund</strong>.
						</div>
						<div id="company401kMatchContributions" class="alert alert-warning">
							<i class="material-icons md-16">subdirectory_arrow_right</i>
							Contribute <strong>{{ company401kMatchContributions }}</strong> to your <strong>401(k)</strong> for your <strong>company match</strong>.
						</div>
						<div id="debtContributions" class="alert alert-danger">
							<i class="material-icons md-16">subdirectory_arrow_right</i>
							Pay off <strong>{{ debtContributions }}</strong> of your <strong>debt</strong>, starting with the <strong>highest interest</strong> loans.
						</div>
						<div id="iraContributions" class="alert alert-success">
							<i class="material-icons md-16">subdirectory_arrow_right</i>
							Contribute <strong>{{ iraContributions }}</strong> to your <strong>Roth or Traditional IRA</strong>.
							<a data-toggle="tooltip" title="Use a Roth IRA if you expect your tax rate to be the same or higher in retirement. Use a Traditional IRA if you expect it to be lower"><span class="glyphicon glyphicon-info-sign"></span></a>
						</div>
						<div id="company401kContributions" class="alert alert-warning">
							<i class="material-icons md-16">subdirectory_arrow_right</i>
							Contribute <strong>{{ company401kContributions }}</strong> to your <strong>401(k)</strong>.
						</div>
						<div id="cash" class="alert alert-info">
							<i class="material-icons md-16">subdirectory_arrow_right</i>
							Contribute the remaining <strong>{{ cash }}</strong> to your <strong>savings and/or investment accounts</strong>.
							<a data-toggle="tooltip" title="Use a savings account for short-term goals (< 5 years) and an investment account for long-term goals (> 10 years)"><span class="glyphicon glyphicon-info-sign"></span></a>
						</div>
					</div>
				</div>
			</div>
			<div id="saving" class="tab-pane fade <?php if (isset($_POST["saveSaving"])) {echo "in active";}?>" ng-init="updateSaving()">
				<div class="row">
					<div class="col-md-1"></div>
					<div class="col-md-10">
						<div class="alert alert-warning" style="font-size:18px;">
							Your savings rate is the most important factor in determining how early you can retire, not the rate of return on your investments.
							This is because increasing your savings rate has a double effect: it increases your retirement savings quicker AND it permanently reduces your expenses, allowing you to retire on less savings.
							Notice the dramatic decrease in the years to retirement when a low savings rate is increased.
							Based on the article from <a href="http://www.mrmoneymustache.com/2012/01/13/the-shockingly-simple-math-behind-early-retirement/" class="alert-link">Mr. Money Mustache</a>.
						</div>
					</div>
					<div class="col-md-1"></div>
				</div>
				<div class="row text-center">
					<label for="savingsRate" style="font-size:1.25em;">Savings Rate</label>
					<a data-toggle="tooltip" title="The percentage of annual income that is saved. The current U.S. personal savings rate is 6.2%"><span class="glyphicon glyphicon-info-sign"></span></a>
					<h1 id="savingsRateText" style="color:#99bc20; margin-top:0px; margin-bottom:15px;">{{ savingsRateText }}</h1>
					<input id="savingsRate" type="range" ng-model="savingsRate" ng-change="updateSaving()">
					<label for="yearsToRetirement" style="margin-top:20px; font-size:1.875em;">Years to Retirement</label>
					<a data-toggle="tooltip" title="Assumes no initial savings, 5% annual returns after inflation, 4% withdrawal rate, and that your expenses remain constant in retirement"><span class="glyphicon glyphicon-info-sign"></span></a>
					<p id="yearsToRetirement" style="color:#99bc20; margin-top:-25px; font-size:6.25em;">{{ yearsToRetirement }}</p>
					<button type="button" class="btn btn-warning" data-toggle="collapse" data-target="#assumptions">Change Assumptions</button>
					<div class="row">
						<div class="col-md-5"></div>
						<div id="assumptions" class="col-md-2 panel panel-default panel-collapse collapse <?php if (isset($_POST["saveSaving"])) {echo "in";}?> panel-body">
							<form method="post" action="/">
								<div class="form-group">
									<label for="initialSavings">Initial Savings:</label>
									<a data-toggle="tooltip" title="As a percentage of current annual savings"><span class="glyphicon glyphicon-info-sign"></span></a>
									<div class="input-group">
										<input id="initialSavings" type="number" class="form-control" name="initialSavings" ng-model="initialSavings" ng-change="updateSaving()">
										<span class="input-group-addon">%</span>
									</div>
								</div>
								<div class="form-group">
									<label for="frontLoadAnnualSavings">Front-Load Annual Savings?</label>
									<a data-toggle="tooltip" title="Put annual savings into accounts at the beginning of the year instead of the end of the year"><span class="glyphicon glyphicon-info-sign"></span></a>
									<div style="margin-bottom:-10px;">
										<label class="switch">
											<input id="frontLoadAnnualSavings" type="checkbox" class="form-control" name="frontLoadAnnualSavings" ng-model="frontLoadAnnualSavings" ng-change="updateSaving()">
											<span class="slider round"></span>
										</label>
									</div>
								</div>
								<div class="form-group">
									<label for="annualReturns">Annual Returns:</label>
									<a data-toggle="tooltip" title="This assumes that you invest all your savings. The annualized inflation-adjusted total returns of the S&P 500 since 1926 is about 7%"><span class="glyphicon glyphicon-info-sign"></span></a>
									<div class="input-group">
										<input id="annualReturns" type="number" class="form-control" name="annualReturns" ng-model="annualReturns" ng-change="updateSaving()">
										<span class="input-group-addon">%</span>
									</div>
								</div>
								<div class="form-group">
									<label for="withdrawalRate">Withdrawal Rate:</label>
									<div class="input-group">
										<input id="withdrawalRate" type="number" class="form-control" name="withdrawalRate" ng-model="withdrawalRate" ng-change="updateSaving()" min="0">
										<span class="input-group-addon">%</span>
									</div>
								</div>
								<div class="form-group">
									<label for="expensesInRetirement">Expenses in Retirement:</label>
									<a data-toggle="tooltip" title="As a percentage of current annual expenses"><span class="glyphicon glyphicon-info-sign"></span></a>
									<div class="input-group">
										<input id="expensesInRetirement" type="number" class="form-control" name="expensesInRetirement" ng-model="expensesInRetirement" ng-change="updateSaving()" min="0">
										<span class="input-group-addon">%</span>
									</div>
								</div>
								<?php
								if (isset($_SESSION['loggedIn'])) {
									echo '<button type="submit" name="saveSaving" class="btn btn-success">Save</button><br><br><br>';
								}
								?>
							</form>
						</div>
						<div class="col-md-5"></div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-1"></div>
	</div>
	<br><br>
	<div class="row">
		<div class="col-md-3"></div>
		<div class="col-md-6">
			<div class="well well-sm text-center">
				<div>GitHub: <a href="https://github.com/tee0402/Bamboo" title="GitHub">https://github.com/tee0402/Bamboo</a></div>
				<div><a href="https://www.flaticon.com/free-icons/bamboo" title="bamboo icons">Bamboo icons created by Freepik - Flaticon</a></div>
			</div>
		</div>
		<div class="col-md-3"></div>
	</div>
</div>

<script>
const company401kContributionsLimitUnder50 = 20500;
const company401kContributionsLimit50OrOlder = 27000;
const iraContributionsLimitUnder50 = 6000;
const iraContributionsLimit50OrOlder = 7000;

$(document).ready(() => {
	<?php
	if (isset($registerError) || isset($loginError)) {
		echo "$('#login').modal('show');";
	}
	?>

    $('[data-toggle="tooltip"]').tooltip();
	
	$("#toggleDiv").click(() => $("#toggleText").text($("#table").is(":visible") ? "Show Calculations" : "Hide Calculations"));

	$('input[type="range"]').rangeslider({
		polyfill: false
	});
});

const app = angular.module('myApp', []);
app.controller('myCtrl', ($scope) => {
	$scope.currentAge = <?php echo isset($_SESSION["currentAge"]) ? $_SESSION["currentAge"] : 25 ?>;
	$scope.targetRetirementAge = <?php echo isset($_SESSION["targetRetirementAge"]) ? $_SESSION["targetRetirementAge"] : 65 ?>;
	$scope.beginningBalance = <?php echo isset($_SESSION["beginningBalance"]) ? $_SESSION["beginningBalance"] : 10000 ?>;
	$scope.annualSavings = <?php echo isset($_SESSION["annualSavings"]) ? $_SESSION["annualSavings"] : 5000 ?>;
	$scope.annualSavingsIncreaseRate = <?php echo isset($_SESSION["annualSavingsIncreaseRate"]) ? $_SESSION["annualSavingsIncreaseRate"] : 0 ?>;
	$scope.expectedAnnualReturn = <?php echo isset($_SESSION["expectedAnnualReturn"]) ? $_SESSION["expectedAnnualReturn"] : 6 ?>;
	$scope.age50OrOlder = <?php echo isset($_SESSION["age50OrOlder"]) ? ($_SESSION["age50OrOlder"] == 1 ? "true" : "false") : "false" ?>;
	$scope.annualIncome = <?php echo isset($_SESSION["annualIncome"]) ? $_SESSION["annualIncome"] : 50000 ?>;
	$scope.monthlyEssentialExpenses = <?php echo isset($_SESSION["monthlyEssentialExpenses"]) ? $_SESSION["monthlyEssentialExpenses"] : 1000 ?>;
	$scope.emergencyFund = <?php echo isset($_SESSION["emergencyFund"]) ? $_SESSION["emergencyFund"] : 0 ?>;
	$scope.debt = <?php echo isset($_SESSION["debt"]) ? $_SESSION["debt"] : 1000 ?>;
	$scope.contributionsThisYear = <?php echo isset($_SESSION["contributionsThisYear"]) ? $_SESSION["contributionsThisYear"] : 0 ?>;
	$scope.company401kMatch = <?php echo isset($_SESSION["company401kMatch"]) ? $_SESSION["company401kMatch"] : 5 ?>;
	$scope.iraContributionsThisYear = <?php echo isset($_SESSION["iraContributionsThisYear"]) ? $_SESSION["iraContributionsThisYear"] : 0 ?>;
	$scope.savingsRate = <?php echo isset($_SESSION["savingsRate"]) ? $_SESSION["savingsRate"] : 5 ?>;
	$scope.initialSavings = <?php echo isset($_SESSION["initialSavings"]) ? $_SESSION["initialSavings"] : 0 ?>;
	$scope.frontLoadAnnualSavings = <?php echo isset($_SESSION["frontLoadAnnualSavings"]) ? ($_SESSION["frontLoadAnnualSavings"] == 1 ? "true" : "false") : "false" ?>;
	$scope.annualReturns = <?php echo isset($_SESSION["annualReturns"]) ? $_SESSION["annualReturns"] : 5 ?>;
	$scope.withdrawalRate = <?php echo isset($_SESSION["withdrawalRate"]) ? $_SESSION["withdrawalRate"] : 4 ?>;
	$scope.expensesInRetirement = <?php echo isset($_SESSION["expensesInRetirement"]) ? $_SESSION["expensesInRetirement"] : 100 ?>;
	$scope.updateCompounding = () => {
		const chartLabels = [];
		const chartData = [];
		const tableData = [];
		let beginningBalance = readNumber($scope.beginningBalance);
		let annualSavings = readNumber($scope.annualSavings);
		const expectedAnnualReturn = $scope.expectedAnnualReturn / 100;
		for (let age = $scope.currentAge; age <= $scope.targetRetirementAge; age++) {
			const interest = beginningBalance * expectedAnnualReturn;
			const endingBalance = beginningBalance + interest + annualSavings;
			chartLabels.push(age);
			chartData.push(beginningBalance.toFixed(2));
			tableData.push({
				age: age,
				beginningBalance: formatCurrency(beginningBalance),
				interest: formatCurrency(interest),
				savings: formatCurrency(annualSavings),
				endingBalance: formatCurrency(endingBalance)
			});
			beginningBalance = endingBalance;
			annualSavings *= 1 + $scope.annualSavingsIncreaseRate / 100;
		}
		if (tableData.length > 0) {
			const lastRow = tableData[tableData.length - 1];
			$scope.endingBalance = lastRow.beginningBalance;
			$scope.annualInterest = lastRow.interest;
		}
		chart.data.labels = chartLabels;
		chart.data.datasets[0].data = chartData;
		chart.update();
		$scope.tableData = tableData;
	};
	$scope.updateSpending = () => {
		let cash = $scope.annualIncome - $scope.monthlyEssentialExpenses * 12;

		if ($scope.monthlyEssentialExpenses > 0) {
			$("#essentialExpenses").show(200);
		} else {
			$("#essentialExpenses").hide(200);
		}
		
		const idealEmergencyFund = $scope.monthlyEssentialExpenses * 6;
		if (cash > 0 && $scope.emergencyFund < idealEmergencyFund) {
			const emergencyFundTopOff = idealEmergencyFund - $scope.emergencyFund;
			$scope.emergencyFundContributions = cash >= emergencyFundTopOff ? emergencyFundTopOff : cash;
			$("#emergencyFundContributions").show(200);
		} else {
			$scope.emergencyFundContributions = 0;
			$("#emergencyFundContributions").hide(200);
		}
		cash -= $scope.emergencyFundContributions;
		$scope.emergencyFundContributions = formatCurrency($scope.emergencyFundContributions);
		
		const company401kMatch = $scope.annualIncome * ($scope.company401kMatch / 100);
		if (cash > 0 && $scope.contributionsThisYear < company401kMatch) {
			const company401kMatchTopOff = company401kMatch - $scope.contributionsThisYear;
			$scope.company401kMatchContributions = cash >= company401kMatchTopOff ? company401kMatchTopOff : cash;
			$("#company401kMatchContributions").show(200);
		} else {
			$scope.company401kMatchContributions = 0;
			$("#company401kMatchContributions").hide(200);
		}
		const totalCompany401kContributions = $scope.contributionsThisYear + $scope.company401kMatchContributions;
		cash -= $scope.company401kMatchContributions;
		$scope.company401kMatchContributions = formatCurrency($scope.company401kMatchContributions);
		
		if (cash > 0 && $scope.debt > 0) {
			$scope.debtContributions = cash >= $scope.debt ? $scope.debt : cash;
			$("#debtContributions").show(200);
		} else {
			$scope.debtContributions = 0;
			$("#debtContributions").hide(200);
		}
		cash -= $scope.debtContributions;
		$scope.debtContributions = formatCurrency($scope.debtContributions);
		
		const iraContributionsLimit = $scope.age50OrOlder ? iraContributionsLimit50OrOlder : iraContributionsLimitUnder50;
		if (cash > 0 && $scope.iraContributionsThisYear < iraContributionsLimit) {
			const iraTopOff = iraContributionsLimit - $scope.iraContributionsThisYear;
			$scope.iraContributions = cash >= iraTopOff ? iraTopOff : cash;
			$("#iraContributions").show(200);
		} else {
			$scope.iraContributions = 0;
			$("#iraContributions").hide(200);
		}
		cash -= $scope.iraContributions;
		$scope.iraContributions = formatCurrency($scope.iraContributions);
		
		const company401kContributionsLimit = $scope.age50OrOlder ? company401kContributionsLimit50OrOlder : company401kContributionsLimitUnder50;
		if (cash > 0 && totalCompany401kContributions < company401kContributionsLimit) {
			const company401kTopOff = company401kContributionsLimit - totalCompany401kContributions;
			$scope.company401kContributions = cash >= company401kTopOff ? company401kTopOff : cash;
			$("#company401kContributions").show(200);
		} else {
			$scope.company401kContributions = 0;
			$("#company401kContributions").hide(200);
		}
		cash -= $scope.company401kContributions;
		$scope.company401kContributions = formatCurrency($scope.company401kContributions);
		
		$scope.cash = formatCurrency(cash);
		if (cash > 0) {
			$("#cash").show(200);
		} else {
			$("#cash").hide(200);
		}
	};
	$scope.updateSaving = () => {
		$scope.savingsRateText = $scope.savingsRate + "%";
		if ($scope.savingsRate == 0) {
			$scope.yearsToRetirement = "Infinite";
		} else {
			const savings = $scope.savingsRate;
			const expenses = (100 - $scope.savingsRate) * ($scope.expensesInRetirement / 100);
			let portfolioValue = $scope.initialSavings / 100 * savings;
			const annualReturns = $scope.annualReturns / 100;
			const withdrawalRate = $scope.withdrawalRate / 100;
			let withdrawal = portfolioValue * withdrawalRate;
			let yearsToRetirement = 0;
			while (withdrawal < expenses) {
				if ($scope.frontLoadAnnualSavings) {
					portfolioValue += savings;
					portfolioValue += portfolioValue * annualReturns;
				} else {
					portfolioValue += portfolioValue * annualReturns + savings;
				}
				withdrawal = portfolioValue * withdrawalRate;
				yearsToRetirement++;
			}
			$scope.yearsToRetirement = yearsToRetirement;
		}
	};
});

Chart.defaults.global.elements.point.hitRadius = 15;
Chart.defaults.global.legend.display = false;
Chart.defaults.global.tooltips.displayColors = false;

const chart = new Chart(document.getElementById("myChart").getContext("2d"), {
    type: "line",
    data: {
        datasets: [{
			borderWidth: 4,
			borderColor: "#99bc20",
            backgroundColor: "#99bc20",
			fill: false,
			lineTension: 0
        }]
    },
    options: {
		title: {
			display: true,
			fontSize: 30,
			text: "Expected Growth of Savings"
		},
		scales: {
			xAxes: [{
				scaleLabel: {
					display: true,
					labelString: "Age"
				}
			}],
			yAxes: [{
				scaleLabel: {
					display: true,
					labelString: "Savings ($)"
				}
			}]
		},
		tooltips: {
			callbacks: {
				title: (tooltipItems, data) => "Age " + tooltipItems[0].xLabel,
				label: (tooltipItem, data) => formatCurrency(tooltipItem.yLabel)
			}
		}
	}
});

function readNumber(number) {
	return typeof number === "number" ? number : 0;
}

function formatCurrency(number) {
	return number.toLocaleString("en-US", {style:"currency", currency:"USD"});
}
</script>
</body>
</html>