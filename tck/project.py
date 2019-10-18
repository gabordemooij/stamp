import getopt,json,sys,os,re
from pathlib import Path

class Project():
	
	def __init__(self, outdir, workdir, parser):
		
		"""
		Initalize the TCK project.
		Creates a TCK project to produce a set
		of template files from a design file.
		
		Usage:
		project = Project( outdir, cwd, Parser() )
		project.process( file1...N ) ...
		"""
		
		self._output_folder      = outdir
		self._working_folder     = workdir
		self._parser             = parser

	def _load_design_file(self, fname):
		f = open(fname,'r')
		tpl = f.read()
		f.close()
		return tpl

	def _check_environment(self, fname):
		if not os.path.isfile(fname):
			raise FileNotFoundError('TCKERR404: Design file not found: {} .'.format(fname))
		if not os.access(self._output_folder, os.W_OK):
			raise PermissionError('TCKERR403: Unable to write to output folder.')
		try:
			os.chdir(self._working_folder)
		except IOError as e:
			raise IOError('TCKERR501: Unable to change to working directory.')
		try:
			template_string = self._load_design_file(fname)
		except IOError as e:
			raise IOError('TCKERR502: Unable to load design file {}.'.format(fname))
		return template_string
	
	def _generate_files(self, blocks):
		generated = []
		for block_name, block in blocks.items():
			block_file = '{}/{}.html'.format( self._output_folder, block_name.replace( '.', '/' ) )
			generated.append( block_file )
			if not os.path.exists( os.path.dirname( block_file ) ):
				os.makedirs( os.path.dirname( block_file ) )
			try:
				with open(block_file,"w+") as f:
					f.write(block)
					f.close()
			except IOError as e:
				print("TCKERR503: Error while writing file: {}".format(f))
		return generated

	def process(self, fname):
		template_string = self._check_environment(fname)
		blocks = self._parser.blocks( template_string, Path(fname).stem )
		return self._generate_files( blocks )
		
		
		
		
		
		
		
		


