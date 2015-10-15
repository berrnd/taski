This is the place where taski looks for applications.

Normally you will create here shell scripts or any other kind of wrappers for existing applications.

The app/script will be started with its working directory set to the output directory and it has to care of:
	- Writing a file "endtime.txt" with an ISO 8601 timestamp of when the task has finished
	- Writing a file "exitcode.txt" with the exit code
