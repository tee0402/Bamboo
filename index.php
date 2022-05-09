<?php
session_start();

$servername = "db5007492839.hosting-data.io";
$dbname = "dbs6173134";
$username = "dbu416653";
$pw = "bamboomysqlpassword";

if (isset($_POST["register"])) {
	if (isset($_POST["registerEmailAddress"]) && isset($_POST["registerPassword"]) && strlen($_POST["registerPassword"]) >= 8) {
		$email = $password = "";
		$email = test_input($_POST["registerEmailAddress"]);
		$password = test_input($_POST["registerPassword"]);
		
		$salt = bin2hex(random_bytes(32));
		$hash = hash_pbkdf2("sha256", $password, $salt, 10000);
		
		try {
			$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $pw);
			$sql = "INSERT INTO Accounts (Email, Password, Salt) VALUES ('$email', '$hash', '$salt')";
			$result = $conn->exec($sql);
			$_SESSION["loggedIn"] = true;
			$_SESSION["email"] = $email;
			$conn = null;
			refresh();
		} catch (PDOException $e) {
			$registerError = "Account already exists. Please login to your account.";
		}
	}
} else if (isset($_POST["login"])) {
	if (isset($_POST["loginEmailAddress"]) && isset($_POST["loginPassword"])) {
		$email = $password = $salt = $hashActual = $hashTry = "";
		$email = test_input($_POST["loginEmailAddress"]);
		$password = test_input($_POST["loginPassword"]);
		
		$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $pw);
	
		$stmt = $conn->prepare("SELECT Password, Salt FROM Accounts WHERE Email='$email'");
		$stmt->execute();
		$row = $stmt->fetch();
	
		if (($row !== false) && ($stmt->rowCount() > 0)) {
			$hashActual = $row["Password"];
			$salt = $row["Salt"];
		}
		$hashTry = hash_pbkdf2("sha256", $password, $salt, 10000);
	
		if ($hashTry === $hashActual) {
			$_SESSION["loggedIn"] = true;
			$_SESSION["email"] = $email;
			refresh();
		} else {
			$loginError = "Incorrect email or password. Please try again.";
		}

		$conn = null;
	}
} else if (isset($_POST["logout"])) {
	session_unset();
	session_destroy();
	refresh();
} else if (isset($_POST["saveCompounding"])) {
	if (isset($_POST["currentAge"]) && isset($_POST["targetRetirementAge"]) && isset($_POST["beginningBalance"]) && isset($_POST["annualSavings"]) && isset($_POST["annualSavingsIncreaseRate"]) && isset($_POST["expectedAnnualReturn"])) {
		$currentAge = $targetRetirementAge = $beginningBalance = $annualSavings = $annualSavingsIncreaseRate = $expectedAnnualReturn = NULL;
		$currentAge = test_input($_POST["currentAge"]);
		$targetRetirementAge = test_input($_POST["targetRetirementAge"]);
		$beginningBalance = test_input($_POST["beginningBalance"]);
		$annualSavings = test_input($_POST["annualSavings"]);
		$annualSavingsIncreaseRate = test_input($_POST["annualSavingsIncreaseRate"]);
		$expectedAnnualReturn = test_input($_POST["expectedAnnualReturn"]);
		
		$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $pw);
		$sql = "UPDATE Accounts SET CurrentAge=$currentAge, TargetRetirementAge=$targetRetirementAge, BeginningBalance=$beginningBalance, AnnualSavings=$annualSavings, AnnualSavingsIncreaseRate=$annualSavingsIncreaseRate, ExpectedAnnualReturn=$expectedAnnualReturn WHERE Email='" . $_SESSION['email'] . "'";
		$conn->exec($sql);
		
		$conn = null;
	}
} else if (isset($_POST["saveSpending"])) {
	if (isset($_POST["age"]) && isset($_POST["annualIncome"]) && isset($_POST["monthlyEssentialExpenses"]) && isset($_POST["emergencyFund"]) && isset($_POST["debt"]) && isset($_POST["contributionsThisYear"]) && isset($_POST["company401kMatch"]) && isset($_POST["iraContributionsThisYear"])) {
		$age = $annualIncome = $monthlyEssentialExpenses = $emergencyFund = $debt = $contributionsThisYear = $company401kMatch = $iraContributionsThisYear = NULL;
		$age = test_input($_POST["age"]);
		$annualIncome = test_input($_POST["annualIncome"]);
		$monthlyEssentialExpenses = test_input($_POST["monthlyEssentialExpenses"]);
		$emergencyFund = test_input($_POST["emergencyFund"]);
		$debt = test_input($_POST["debt"]);
		$contributionsThisYear = test_input($_POST["contributionsThisYear"]);
		$company401kMatch = test_input($_POST["company401kMatch"]);
		$iraContributionsThisYear = test_input($_POST["iraContributionsThisYear"]);
		
		$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $pw);
		$sql = "UPDATE Accounts SET Age=$age, AnnualIncome=$annualIncome, MonthlyEssentialExpenses=$monthlyEssentialExpenses, EmergencyFund=$emergencyFund, Debt=$debt, ContributionsThisYear=$contributionsThisYear, Company401kMatch=$company401kMatch, IRAContributionsThisYear=$iraContributionsThisYear WHERE Email='" . $_SESSION['email'] . "'";
		$conn->exec($sql);
		
		$conn = null;
	}
}

if (isset($_SESSION["loggedIn"])) {
	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $pw);
	
	$stmt = $conn->prepare("SELECT CurrentAge, TargetRetirementAge, BeginningBalance, AnnualSavings, AnnualSavingsIncreaseRate, ExpectedAnnualReturn, Age, AnnualIncome, MonthlyEssentialExpenses, EmergencyFund, Debt, ContributionsThisYear, Company401kMatch, IRAContributionsThisYear FROM Accounts WHERE Email='" . $_SESSION['email'] . "'");
	$stmt->execute();
	$row = $stmt->fetch();
	if ($row["CurrentAge"] != NULL && $row["TargetRetirementAge"] != NULL && $row["BeginningBalance"] != NULL && $row["AnnualSavings"] != NULL && $row["AnnualSavingsIncreaseRate"] != NULL && $row["ExpectedAnnualReturn"] != NULL) {
		$_SESSION["currentAge"] = $row["CurrentAge"];
		$_SESSION["targetRetirementAge"] = $row["TargetRetirementAge"];
		$_SESSION["beginningBalance"] = $row["BeginningBalance"];
		$_SESSION["annualSavings"] = $row["AnnualSavings"];
		$_SESSION["annualSavingsIncreaseRate"] = $row["AnnualSavingsIncreaseRate"];
		$_SESSION["expectedAnnualReturn"] = $row["ExpectedAnnualReturn"];
	}
	if ($row["Age"] != NULL && $row["AnnualIncome"] != NULL && $row["MonthlyEssentialExpenses"] != NULL && $row["EmergencyFund"] != NULL && $row["Debt"] != NULL && $row["ContributionsThisYear"] != NULL && $row["Company401kMatch"] != NULL && $row["IRAContributionsThisYear"] != NULL) {
		$_SESSION["age"] = $row["Age"];
		$_SESSION["annualIncome"] = $row["AnnualIncome"];
		$_SESSION["monthlyEssentialExpenses"] = $row["MonthlyEssentialExpenses"];
		$_SESSION["emergencyFund"] = $row["EmergencyFund"];
		$_SESSION["debt"] = $row["Debt"];
		$_SESSION["contributionsThisYear"] = $row["ContributionsThisYear"];
		$_SESSION["company401kMatch"] = $row["Company401kMatch"];
		$_SESSION["iraContributionsThisYear"] = $row["IRAContributionsThisYear"];
	}
	
	$conn = null;
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

function refresh() {
	header('location: index.php');
	exit;
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
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/rangeslider.js/2.3.0/rangeslider.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.4/angular.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.bundle.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/rangeslider.js/2.3.0/rangeslider.min.js"></script>
	<title>Bamboo - Personal Finance Utility</title>
	<style>
	.nav-pills > li.active > a, .nav-pills > li.active > a:hover, .nav-pills > li.active > a:focus {
		background-color: #99bc20;
	}
	.material-icons.md-16 { font-size: 16px; }
	</style>
</head>
<body>

<div id="container" class="container-fluid" ng-app="myApp" ng-controller="myCtrl">
	<div class="row">
		<div class="page-header">
			<?php
			if (isset($_SESSION['loggedIn'])) {
				echo '<form method="post" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '">
				<button type="submit" name="logout" class="btn btn-success pull-right" style="margin-right:30px;">Logout</button>
				</form>
				<h5 class="pull-right" style="margin-right:20px;">' . $_SESSION["email"] . '</h5>';
			} else {
				echo '<button type="button" class="btn btn-success pull-right" data-toggle="modal" data-target="#register" style="margin-right:30px;">Register</button>
				<button type="button" class="btn btn-success pull-right" data-toggle="modal" data-target="#login" style="margin-right:20px;">Login</button>';
			}
			?>
			<a href="." style="text-decoration:none;"><h1 style="margin-left:30px; color:#99bc20;"><strong>Bamboo</strong><img src="bamboo.png" height="33px" style="margin-left:5px;"></h1></a>
			<h4 style="margin-left:30px;"><small>Grow your savings for financial independence or retirement</small></h4>
			<div id="register" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Register</h4>
						</div>
						<div class="modal-body">
							<form id="formRegister" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
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
							<form id="formLogin" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
								<?php
									if (isset($registerError)) {
										echo "<div class='alert alert-danger'>$registerError</div>";
									}
								?>
								<?php
									if (isset($loginError)) {
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
				<li id="compoundingPill" class="active"><a data-toggle="pill" href="#compounding">Compounding</a></li>
				<li id="spendingPill"><a data-toggle="pill" href="#spending">Spending Prioritization</a></li>
				<li id="savingPill"><a data-toggle="pill" href="#saving">Years to Retirement</a></li>
			</ul>
			<br>
		</div>
		<div class="col-md-9 tab-content">
			<div id="compounding" class="tab-pane fade in active" ng-init="updateChart()">
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
						<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
							<div class="form-group">
								<label for="currentAge">Current Age:</label>
								<div class="input-group">
									<input id="currentAge" type="number" class="form-control" name="currentAge" ng-model="currentAge" ng-change="updateChart()">
									<span class="input-group-addon">Years</span>
								</div>
							</div>
							<div class="form-group">
								<label for="targetRetirementAge">Target Retirement Age:</label>
								<div class="input-group">
									<input id="targetRetirementAge" type="number" class="form-control" name="targetRetirementAge" ng-model="targetRetirementAge" ng-change="updateChart()">
									<span class="input-group-addon">Years</span>
								</div>
							</div>
							<div class="form-group">
								<label for="beginningBalance">Beginning Balance:</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="glyphicon glyphicon-usd"></i></span>
									<input id="beginningBalance" type="number" class="form-control" name="beginningBalance" ng-model="beginningBalance" ng-change="updateChart()">
								</div>
							</div>
							<div class="form-group">
								<label for="annualSavings">Annual Savings:</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="glyphicon glyphicon-usd"></i></span>
									<input id="annualSavings" type="number" class="form-control" name="annualSavings" ng-model="annualSavings" ng-change="updateChart()">
								</div>
							</div>
							<div class="form-group">
								<label for="annualSavingsIncreaseRate">Annual Savings Increase Rate:</label>
								<a data-toggle="tooltip" title="The percentage increase in your savings amount per year"><span class="glyphicon glyphicon-info-sign"></span></a>
								<div class="input-group">
									<input id="annualSavingsIncreaseRate" type="number" class="form-control" name="annualSavingsIncreaseRate" ng-model="annualSavingsIncreaseRate" ng-change="updateChart()">
									<span class="input-group-addon">%</span>
								</div>
							</div>
							<div class="form-group">
								<label for="expectedAnnualReturn">Expected Annual Return:</label>
								<a data-toggle="tooltip" title="This assumes that you invest all your savings. The historical annualized return of the Dow, adjusted for inflation, is 6-7%"><span class="glyphicon glyphicon-info-sign"></span></a>
								<div class="input-group">
									<input id="expectedAnnualReturn" type="number" class="form-control" name="expectedAnnualReturn" ng-model="expectedAnnualReturn" ng-change="updateChart()" min="0" max="20">
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
						<div id="chart">
							<canvas id="myChart"></canvas>
						</div>
					</div>
				</div>
				<br>
				<div class="row">
					<div class="col-md-12">
						<div class="panel-group">
							<div class="panel panel-success">
								<div class="panel-heading">
									<h4 class="panel-title text-center">
										<a id="toggle" data-toggle="collapse" href="#table" style="text-decoration:underline;">Show Calculations</a>
									</h4>
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
													<tr ng-repeat="x in tableData">
														<td>{{ x.age }}</td>
														<td>{{ x.beginningBalance }}</td>
														<td>{{ x.interest }}</td>
														<td>{{ x.savings }}</td>
														<td>{{ x.endingBalance }}</td>
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
				</div>
			</div>
			<div id="spending" class="tab-pane fade" ng-init="updateContributions()">
				<div class="row">
					<div class="col-md-1"></div>
					<div class="col-md-10">
						<div class="alert alert-info" style="font-size:18px;">
							You can spend your income in a way that benefits you in the long run.
							You can do so by focusing on the more important priorities first.
							Those are the ones that would give you the maximum benefits for your money, such as building up an ample emergency fund (6 months of expenses), taking advantage of free money such as a company 401(k) match, minimizing interest payments by eliminating high-interest debt, and contributing to your tax-deferred retirement accounts before contributing to your taxable ones.
							Based on the flowchart from <a href="https://www.reddit.com/r/personalfinance/comments/4gdlu9/how_to_prioritize_spending_your_money_a_flowchart/" class="alert-link">Reddit</a>.
						</div>
					</div>
					<div class="col-md-1"></div>
				</div>
				<div class="row">
					<div class="col-md-3">
						<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
							<div class="form-group">
								<label for="age">Age:</label>
								<div class="input-group">
									<input id="age" type="number" class="form-control" name="age" ng-model="age" ng-change="updateContributions()">
									<span class="input-group-addon">Years</span>
								</div>
							</div>
							<div class="form-group">
								<label for="annualIncome">Annual Income:</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="glyphicon glyphicon-usd"></i></span>
									<input id="annualIncome" type="number" class="form-control" name="annualIncome" ng-model="annualIncome" ng-change="updateContributions()">
								</div>
							</div>
							<div class="form-group">
								<label for="monthlyEssentialExpenses">Monthly Essential Expenses:</label>
								<a data-toggle="tooltip" title="Rent, utilities, food, insurance, minimum payments, etc."><span class="glyphicon glyphicon-info-sign"></span></a>
								<div class="input-group">
									<span class="input-group-addon"><i class="glyphicon glyphicon-usd"></i></span>
									<input id="monthlyEssentialExpenses" type="number" class="form-control" name="monthlyEssentialExpenses" ng-model="monthlyEssentialExpenses" ng-change="updateContributions()">
								</div>
							</div>
							<div class="form-group">
								<label for="emergencyFund">Emergency Fund:</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="glyphicon glyphicon-usd"></i></span>
									<input id="emergencyFund" type="number" class="form-control" name="emergencyFund" ng-model="emergencyFund" ng-change="updateContributions()">
								</div>
							</div>
							<div class="form-group">
								<label for="debt">Debt:</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="glyphicon glyphicon-usd"></i></span>
									<input id="debt" type="number" class="form-control" name="debt" ng-model="debt" ng-change="updateContributions()">
								</div>
							</div>
							<div class="form-group">
								<label for="401kContributionsThisYear">401(k) Contributions This Year:</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="glyphicon glyphicon-usd"></i></span>
									<input id="contributionsThisYear" type="number" class="form-control" name="contributionsThisYear" ng-model="contributionsThisYear" ng-change="updateContributions()">
								</div>
							</div>
							<div class="form-group">
								<label for="company401kMatch">Company 401(k) % Match:</label>
								<a data-toggle="tooltip" title="The percentage of gross income that the employer matches up to. Enter 0 if your company does not match 401(k) contributions"><span class="glyphicon glyphicon-info-sign"></span></a>
								<div class="input-group">
									<input id="company401kMatch" type="number" class="form-control" name="company401kMatch" ng-model="company401kMatch" ng-change="updateContributions()">
									<span class="input-group-addon">%</span>
								</div>
							</div>
							<div class="form-group">
								<label for="iraContributionsThisYear">IRA Contributions This Year:</label>
								<a data-toggle="tooltip" title="Roth and Traditional combined"><span class="glyphicon glyphicon-info-sign"></span></a>
								<div class="input-group">
									<span class="input-group-addon"><i class="glyphicon glyphicon-usd"></i></span>
									<input id="iraContributionsThisYear" type="number" class="form-control" name="iraContributionsThisYear" ng-model="iraContributionsThisYear" ng-change="updateContributions()">
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
			<div id="saving" class="tab-pane fade">
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
				<div class="row">
					<div class="col-md-12 text-center">
						<label for="savingsRate" style="font-size:1.25em;">Savings Rate</label>
						<a data-toggle="tooltip" title="The percentage of annual income that is saved. The current U.S. personal savings rate is 5.3%"><span class="glyphicon glyphicon-info-sign"></span></a>
						<h1 id="sliderText" style="color:#99bc20; margin-top:0px; margin-bottom:15px;"></h1>
						<input id="slider" type="range" value="5">
						<label for="yearsToRetirement" style="margin-top:20px; font-size:1.875em;">Years to Retirement</label>
						<a data-toggle="tooltip" title="Assumes 5% annual returns after inflation, 4% withdrawal rate, and that your expenses remain constant in retirement"><span class="glyphicon glyphicon-info-sign"></span></a>
						<p id="yearsToRetirement" style="color:#99bc20; margin-top:-25px; font-size:6.25em;"></p>
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
			<div class="navbar navbar-default text-center" style="padding:10px;">
				Icons made by <a href="http://www.freepik.com" title="Freepik">Freepik</a> from <a href="http://www.flaticon.com" title="Flaticon">www.flaticon.com</a> is licensed by <a href="http://creativecommons.org/licenses/by/3.0/" title="Creative Commons BY 3.0" target="_blank">CC 3.0 BY</a>
			</div>
		</div>
		<div class="col-md-3"></div>
	</div>
</div>

<script>
<?php
if (isset($registerError) || isset($loginError)) {
	echo "$(document).ready(function() {
		$('#login').modal('show');
	});";
}
?>

$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();
	
	$("#toggle").click(function() {
		$("#toggle").text($("#toggle").text() == "Show Calculations" ? "Hide Calculations" : "Show Calculations");
	});

	$("#compoundingPill").click(function() {
		setCookie("tab", "compounding");
	});
	$("#spendingPill").click(function() {
		setCookie("tab", "spending");
	});
	$("#savingPill").click(function() {
		setCookie("tab", "saving");
	});

	$('input[type="range"]').rangeslider({
		// Feature detection the default is `true`.
		// Set this to `false` if you want to use
		// the polyfill also in Browsers which support
		// the native <input type="range"> element.
		polyfill: false,

		// Default CSS classes
		rangeClass: 'rangeslider',
		disabledClass: 'rangeslider--disabled',
		horizontalClass: 'rangeslider--horizontal',
		verticalClass: 'rangeslider--vertical',
		fillClass: 'rangeslider__fill',
		handleClass: 'rangeslider__handle',

		// Callback function
		onInit: function() {
			$("#sliderText").text($("#slider").val() + "%");
			$("#yearsToRetirement").text(calculateYearsToRetirement(Number($("#slider").val())));
		},

		// Callback function
		onSlide: function(position, value) {
			$("#sliderText").text($("#slider").val() + "%");
			$("#yearsToRetirement").text(calculateYearsToRetirement(Number($("#slider").val())));
		},

		// Callback function
		onSlideEnd: function(position, value) {}
	});

	let tab = getCookie("tab");
	if (tab != "") {
		if (tab == "spending") {
			$("#compoundingPill").removeClass("active");
			$("#compounding").removeClass("in");
			$("#compounding").removeClass("active");
			$("#spendingPill").addClass("active");
			$("#spending").addClass("in");
			$("#spending").addClass("active");
		} else if (tab == "saving") {
			$("#compoundingPill").removeClass("active");
			$("#compounding").removeClass("in");
			$("#compounding").removeClass("active");
			$("#savingPill").addClass("active");
			$("#saving").addClass("in");
			$("#saving").addClass("active");
		}
	}
});

let app = angular.module('myApp', []);
app.controller('myCtrl', function($scope) {
	$scope.currentAge = <?php echo isset($_SESSION["currentAge"]) ? $_SESSION["currentAge"] : 25 ?>;
	$scope.targetRetirementAge = <?php echo isset($_SESSION["targetRetirementAge"]) ? $_SESSION["targetRetirementAge"] : 65 ?>;
	$scope.beginningBalance = <?php echo isset($_SESSION["beginningBalance"]) ? $_SESSION["beginningBalance"] : 10000 ?>;
	$scope.annualSavings = <?php echo isset($_SESSION["annualSavings"]) ? $_SESSION["annualSavings"] : 5000 ?>;
	$scope.annualSavingsIncreaseRate = <?php echo isset($_SESSION["annualSavingsIncreaseRate"]) ? $_SESSION["annualSavingsIncreaseRate"] : 0 ?>;
	$scope.expectedAnnualReturn = <?php echo isset($_SESSION["expectedAnnualReturn"]) ? $_SESSION["expectedAnnualReturn"] : 6 ?>;
	$scope.age = <?php echo isset($_SESSION["age"]) ? $_SESSION["age"] : 25 ?>;
	$scope.annualIncome = <?php echo isset($_SESSION["annualIncome"]) ? $_SESSION["annualIncome"] : 50000 ?>;
	$scope.monthlyEssentialExpenses = <?php echo isset($_SESSION["monthlyEssentialExpenses"]) ? $_SESSION["monthlyEssentialExpenses"] : 1000 ?>;
	$scope.emergencyFund = <?php echo isset($_SESSION["emergencyFund"]) ? $_SESSION["emergencyFund"] : 0 ?>;
	$scope.debt = <?php echo isset($_SESSION["debt"]) ? $_SESSION["debt"] : 1000 ?>;
	$scope.contributionsThisYear = <?php echo isset($_SESSION["contributionsThisYear"]) ? $_SESSION["contributionsThisYear"] : 0 ?>;
	$scope.company401kMatch = <?php echo isset($_SESSION["company401kMatch"]) ? $_SESSION["company401kMatch"] : 5 ?>;
	$scope.iraContributionsThisYear = <?php echo isset($_SESSION["iraContributionsThisYear"]) ? $_SESSION["iraContributionsThisYear"] : 0 ?>;
	$scope.updateContributions = function() {
		if ($scope.monthlyEssentialExpenses > 0) {
			$("#essentialExpenses").show(200);
		} else {
			$("#essentialExpenses").hide(200);
		}
		
		let idealEmergencyFund = $scope.monthlyEssentialExpenses * 6;
		let cash = $scope.annualIncome - $scope.monthlyEssentialExpenses * 12;
		if ($scope.emergencyFund >= idealEmergencyFund) { // Current emergency fund is sufficient
			$scope.emergencyFundContributions = 0;
			$("#emergencyFundContributions").hide(200);
		} else { // Current emergency fund is not sufficient
			if (cash > 0) { // Enough cash for contributions to emergency fund
				let emergencyFundTopOff = idealEmergencyFund - $scope.emergencyFund;
				if (cash >= emergencyFundTopOff) { // Enough cash to top off emergency fund - top it off
					$scope.emergencyFundContributions = emergencyFundTopOff;
				} else { // Not enough cash to top off emergency fund - just contribute all of it
					$scope.emergencyFundContributions = cash;
				}
				$("#emergencyFundContributions").show(200);
			} else { // Not enough cash for contributions to emergency fund
				$scope.emergencyFundContributions = 0;
				$("#emergencyFundContributions").hide(200);
			}
		}
		cash -= $scope.emergencyFundContributions;
		$scope.emergencyFundContributions = "$" + $scope.emergencyFundContributions.toFixed(2).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
		
		let company401kMatch = $scope.annualIncome * ($scope.company401kMatch / 100);
		let totalCompany401kContributions;
		if ($scope.contributionsThisYear >= company401kMatch) {
			$scope.company401kMatchContributions = 0;
			$("#company401kMatchContributions").hide(200);
		} else {
			if (cash > 0) {
				let company401kMatchTopOff = company401kMatch - $scope.contributionsThisYear;
				if (cash >= company401kMatchTopOff) {
					$scope.company401kMatchContributions = company401kMatchTopOff;
				} else {
					$scope.company401kMatchContributions = cash;
				}
				$("#company401kMatchContributions").show(200);
			} else {
				$scope.company401kMatchContributions = 0;
				$("#company401kMatchContributions").hide(200);
			}
		}
		totalCompany401kContributions = $scope.contributionsThisYear + $scope.company401kMatchContributions;
		cash -= $scope.company401kMatchContributions;
		$scope.company401kMatchContributions = "$" + $scope.company401kMatchContributions.toFixed(2).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
		
		if ($scope.debt > 0) {
			if (cash > 0) {
				if (cash >= $scope.debt) {
					$scope.debtContributions = $scope.debt;
				} else {
					$scope.debtContributions = cash;
				}
				$("#debtContributions").show(200);
			} else {
				$scope.debtContributions = 0;
				$("#debtContributions").hide(200);
			}
		} else {
			$scope.debtContributions = 0;
			$("#debtContributions").hide(200);
		}
		cash -= $scope.debtContributions;
		$scope.debtContributions = "$" + $scope.debtContributions.toFixed(2).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
		
		let iraContributionsLimit = 5500;
		if ($scope.age >= 50) {
			iraContributionsLimit = 6500;
		}
		if ($scope.iraContributionsThisYear >= iraContributionsLimit) {
			$scope.iraContributions = 0;
			$("#iraContributions").hide(200);
		} else {
			if (cash > 0) {
				let iraTopOff = iraContributionsLimit - $scope.iraContributionsThisYear;
				if (cash >= iraTopOff) {
					$scope.iraContributions = iraTopOff;
				} else {
					$scope.iraContributions = cash;
				}
				$("#iraContributions").show(200);
			} else {
				$scope.iraContributions = 0;
				$("#iraContributions").hide(200);
			}
		}
		cash -= $scope.iraContributions;
		$scope.iraContributions = "$" + $scope.iraContributions.toFixed(2).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
		
		let company401kContributionsLimit = 18000;
		if ($scope.age >= 50) {
			company401kContributionsLimit = 24000;
		}
		if (totalCompany401kContributions >= company401kContributionsLimit) {
			$scope.company401kContributions = 0;
			$("#company401kContributions").hide(200);
		} else {
			if (cash > 0) {
				let company401kTopOff = company401kContributionsLimit - totalCompany401kContributions;
				if (cash >= company401kTopOff) {
					$scope.company401kContributions = company401kTopOff;
				} else {
					$scope.company401kContributions = cash;
				}
				$("#company401kContributions").show(200);
			} else {
				$scope.company401kContributions = 0;
				$("#company401kContributions").hide(200);
			}
		}
		cash -= $scope.company401kContributions;
		$scope.company401kContributions = "$" + $scope.company401kContributions.toFixed(2).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
		
		if (cash > 0) {
			$scope.cash = cash;
			$("#cash").show(200);
		} else {
			$scope.cash = 0;
			$("#cash").hide(200);
		}
		$scope.cash = "$" + $scope.cash.toFixed(2).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
	};
	$scope.updateChart = function() {
		let tableData = [];
		let myLabels = [];
		let myData = [];
		let beginningBalance = $scope.beginningBalance;
		let annualSavings = $scope.annualSavings;
		let years = $scope.targetRetirementAge - $scope.currentAge;
		for (let i = 0; i <= years; i++) {
			myLabels.push($scope.currentAge + i);
			myData.push(beginningBalance.toFixed(2));
			let tableObject = {
				age: $scope.currentAge + i,
				beginningBalance: "$" + beginningBalance.toFixed(2).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,"),
				interest: "$" + (beginningBalance * ($scope.expectedAnnualReturn / 100)).toFixed(2).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,"),
				savings: "$" + annualSavings.toFixed(2).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,"),
				endingBalance: "$" + (beginningBalance * (1 + $scope.expectedAnnualReturn / 100) + annualSavings).toFixed(2).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,")
			};
			tableData.push(tableObject);
			if (i == years) {
				$scope.endingBalance = "$" + beginningBalance.toFixed(2).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
				$scope.annualInterest = "$" + (beginningBalance * ($scope.expectedAnnualReturn / 100)).toFixed(2).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
			}
			beginningBalance = beginningBalance * (1 + $scope.expectedAnnualReturn / 100) + annualSavings;
			annualSavings = annualSavings * (1 + $scope.annualSavingsIncreaseRate / 100);
		}
		$scope.tableData = tableData;
		setData(chart, myLabels, myData);
	};
});

Chart.defaults.global.elements.point.hitRadius = 15;
Chart.defaults.global.legend.display = false;
Chart.defaults.global.tooltips.displayColors = false;

let ctx = document.getElementById("myChart").getContext("2d");
let chart = new Chart(ctx, {
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
		}
	}
});

function setData(chart, labels, data) {
    chart.data.labels = labels;
    chart.data.datasets[0].data = data;
    chart.update();
}

function calculateYearsToRetirement(savingsRate) {
	if (savingsRate == 0) {
		return "Infinite";
	}
	let savings = savingsRate;
	let expenses = 100 - savingsRate;
	let portfolioValue = 0;
	let annualReturn = 0.05;
	let withdrawalRate = 0.04;
	let withdrawal = portfolioValue * withdrawalRate;
	let interest;
	let yearsToRetirement = 0;
	while (withdrawal < expenses) {
		portfolioValue += savings;
		interest = portfolioValue * annualReturn;
		portfolioValue += interest;
		withdrawal = portfolioValue * withdrawalRate;
		yearsToRetirement++;
	}
	return yearsToRetirement;
}

function setCookie(cname, cvalue) {
    document.cookie = cname + "=" + cvalue + ";";
}

function getCookie(cname) {
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}
</script>

</body>
</html>