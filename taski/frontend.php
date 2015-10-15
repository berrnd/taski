<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>taski on <?php echo gethostname(); ?></title>

    <link rel="shortcut icon" href="glyphicons-419-disk-import.png">
    <link href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="taski.css" rel="stylesheet">
</head>
<body>
    <div class="container">

        <div class="header clearfix">
            <h3 class="text-muted">taski <small>running on <?php echo gethostname(); ?></small></h3>
        </div>

        <div class="row main-content">

            <div class="col-lg-6 col-lg-offset-3">
                <h4>Add task</h4>
                <form id="addTaskForm">
                    <div class="form-group">
                        <label for="inputApp">App</label>
                        <select id="inputApp" class="form-control">
                            <?php
                            foreach ($data['apps'] as $app)
                            {
                                echo '<option>'.$app.'</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="inputParams">Parameters</label>
                        <input id="inputParams" type="text" class="form-control" placeholder="Params...">
                    </div>

                    <button type="submit" class="btn btn-default">Add task</button>
                </form>
            </div>

            <div class="col-lg-12">
                <h4>Task overview</h4>
                <div class="table-responsive">
                    <table id="tasksTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>App</th>
                                <th>Start time</th>
                                <th>End time</th>
                                <th>Duration</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($data['tasks'] as $task)
                            {
                                $statusClass = 'danger';
                                if ($task['status'] == 'running')
                                    $statusClass = 'active';
                                else
                                    if ($task['exitCode'] === '0' || $task['exitCode'] == '"0"' || $task['exitCode'] == "0\r\n" || $task['exitCode'] === "\"0\"\r\n")
                                        $statusClass = 'success';

                                echo '<tr class="' . $statusClass .'" id="'.$task['id'].'"><td>'.$task['app'].'</td><td class="moment-timestamp" data-timestamp="'.$task['startTime'].'">'.$task['startTime'].'</td><td class="moment-timestamp" data-timestamp="'.$task['endTime'].'">'.$task['endTime'].'</td><td class="moment-duration" data-start="'.$task['startTime'].'" data-end="'.$task['endTime'].'">durationx</td><td>'.$task['status'].'</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <footer class="footer">
            <small>Created with <span class="glyphicon glyphicon-heart" aria-hidden="true"></span> by <a target="_blank" href="https://berrnd.de">Bernd Bestel</a><br />
                <a target="_blank" href="https://github.com/berrnd/taski">Project page</a> // <?php echo file_get_contents('version.txt'); ?>
            </small>
        </footer>

    </div>

    <?php foreach ($data['tasks'] as $task) : ?>
    <div id="taskDetails_<?php echo $task['id']; ?>" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Task details (id <?php echo $task['id']; ?>)</h4>
                </div>
                <div class="modal-body">
                    <h5>Command</h5>
                    <pre class="pre-scrollable"><?php echo $task['cmd']; ?></pre>
                    <h5>Output</h5>
                    <pre class="pre-scrollable"><?php echo $task['output']; ?></pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>


    <script src="components/jquery/jquery.min.js"></script>
    <script src="vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="components/moment/min/moment.min.js"></script>

    <script>
        $(function () {
            $('#addTaskForm').on('submit', function (e) {
                e.preventDefault();
                var app = $("#inputApp").val();
                var params = $("#inputParams").val();

                $.ajax({
                    'url': 'index.php/api/add-task',
                    'type': 'POST',
                    'data': {
                        'app': app,
                        'params': params
                    },
                    'success': function (data) {
                        location.reload();
                    }
                });
            });

            $('#tasksTable > tbody > tr').click(function (e) {
                var taskId = $(this).parent('tr').context.id;
                var modalId = 'taskDetails_' + taskId;
                $('#' + modalId).modal('show');
            });

            $('.moment-timestamp').each(function () {
                var element = $(this);
                var timestamp = element.data('timestamp');
                element.text(moment(timestamp).fromNow());
            });

            $('.moment-duration').each(function () {
                var element = $(this);
                var start = element.data('start');
                var end = element.data('end');
                var duration = moment.duration(moment(end).subtract(moment(start)));
                element.text(duration.humanize());
            });
        });
    </script>
</body>
</html>
