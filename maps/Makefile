# Makefile for Pennsic Maps

# PDFS = $(wildcard *.pdf)
# (not working for some reason, trying this instead)
# PDFS := $(shell ls *.pdf)
# (nope, doing this instead)
PDFS := $(sort `ls *.pdf`) /dev/null

PDF1 := $(filter %_L.pdf,$(PDFS))
PDF2 := $(filter pennsic%.pdf,$(PDFS))

PNG1 := $(patsubst %_L.pdf,%_S.png,$(PDF1))

PNG2 := $(patsubst pennsic%.pdf,pennsic%_L.png,$(PDF2))
GIF2 := $(patsubst pennsic%.pdf,pennsic%.gif,$(PDF2))

all: $(PNG1) $(PNG2) $(GIF2)

count:
	@echo $(PNGS) | wc -w

%_S.png: %_L.pdf
	@echo "$< -> $@"
	time convert -density 600x600 -quality 90 -geometry 750x $< $@.tmp.png
	@mv -iv $@.tmp.png $@

pennsic%_L.png: pennsic%.pdf
	@echo "$< -> $@"
	time convert -density 600x600 -quality 90 -geometry 1500x $< $@.tmp.png
	@mv -iv $@.tmp.png $@

pennsic%.gif: pennsic%_L.png
	@echo "$< -> $@"
	time convert -density 600x600 -quality 90 -geometry 750x $< $@.tmp.gif
	@mv -iv $@.tmp.gif $@

test:
	#@echo PDFS: $(PDFS)
	@echo PDF1: $(PDFS)
	@echo PDF2: $(PDFS)
	@echo PNG1: $(PNGS)
	@echo PNG2: $(PNG2)
	@echo GIF2: $(GIF2)

