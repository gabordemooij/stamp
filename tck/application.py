from getopt import getopt
from sys import argv
from tck.project import Project
from tck.parser  import Parser
from tck.help    import Help

class Application():

	@staticmethod
	def run():
		if len(argv)>1:
			try:
				opts, args = getopt(argv[1:], 'w:o:')
				outdir = ''
				workdir = ''
				if len(opts):
					for opt,arg in opts:
						if (opt == '-o'):
							outdir = arg
						if (opt == '-w'):
							workdir = arg
				project = Project( outdir, workdir, Parser() )
				for design_file in args:
					print( '\n'.join(project.process( design_file )) )
				exit(0)
			except Exception as error:
				print("Error:")
				print(error)
				exit(1)
		else:
			Help.print_manual()
			exit(0)
