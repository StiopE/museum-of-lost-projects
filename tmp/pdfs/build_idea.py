from reportlab.lib import colors
from reportlab.lib.enums import TA_LEFT
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import mm
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak, Flowable, KeepTogether
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.pdfbase import pdfmetrics
from reportlab.lib.colors import HexColor

OUT='output/pdf/museum-of-failed-projects-idea.pdf'
INK=HexColor('#22252A'); MUTED=HexColor('#59616D'); PAPER=HexColor('#F7F5F0'); ACCENT=HexColor('#C45B3D'); TEAL=HexColor('#2E6E68'); LINE=HexColor('#DAD7D0'); LIGHT=HexColor('#EEEAE1')
styles=getSampleStyleSheet()
styles.add(ParagraphStyle(name='Eyebrow', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=8, leading=10, textColor=ACCENT, spaceAfter=4, tracking=1.2))
styles.add(ParagraphStyle(name='TitleX', parent=styles['Title'], fontName='Helvetica-Bold', fontSize=27, leading=30, textColor=INK, alignment=TA_LEFT, spaceAfter=5))
styles.add(ParagraphStyle(name='Sub', parent=styles['Normal'], fontName='Helvetica', fontSize=10, leading=14, textColor=MUTED, spaceAfter=8))
styles.add(ParagraphStyle(name='H2x', parent=styles['Heading2'], fontName='Helvetica-Bold', fontSize=14, leading=17, textColor=INK, spaceBefore=5, spaceAfter=7))
styles.add(ParagraphStyle(name='H3x', parent=styles['Heading3'], fontName='Helvetica-Bold', fontSize=9.6, leading=12, textColor=TEAL, spaceBefore=3, spaceAfter=2))
styles.add(ParagraphStyle(name='BodyX', parent=styles['BodyText'], fontName='Helvetica', fontSize=9.4, leading=13, textColor=INK, spaceAfter=4))
styles.add(ParagraphStyle(name='SmallX', parent=styles['BodyText'], fontName='Helvetica', fontSize=7.8, leading=10, textColor=MUTED))
styles.add(ParagraphStyle(name='CellX', parent=styles['BodyText'], fontName='Helvetica', fontSize=8, leading=10, textColor=INK))
styles.add(ParagraphStyle(name='CellTitle', parent=styles['BodyText'], fontName='Helvetica-Bold', fontSize=9, leading=11, textColor=INK))

class ModelMap(Flowable):
    def __init__(self): super().__init__(); self.width=174*mm; self.height=92*mm
    def draw(self):
        c=self.canv
        boxes={
            'User':(0,58,52,29,'id, name, email, password, is_admin'),
            'Project':(61,46,66,41,'id, user_id, category_id, parent_project_id\ntitle, story, status, adoption_available'),
            'Category':(137,58,37,29,'id, name, slug'),
            'AdoptionRequest':(0,4,52,32,'project_id, user_id, message\nstatus, revived_project_id'),
            'ProjectUpdate':(61,4,52,29,'project_id, user_id, content'),
            'ProjectImage':(122,4,52,29,'project_id, image_path\ncaption, sort_order'),
        }
        # arrows behind boxes
        c.setStrokeColor(TEAL); c.setLineWidth(1.2)
        def arrow(x1,y1,x2,y2,label):
            x1,y1,x2,y2=[v*mm for v in (x1,y1,x2,y2)]
            c.line(x1,y1,x2,y2); c.line(x2,y2,x2-3,y2+2); c.line(x2,y2,x2-3,y2-2)
            c.setFillColor(MUTED); c.setFont('Helvetica',6.5); c.drawCentredString((x1+x2)/2,(y1+y2)/2+2,label)
        arrow(52,73,61,68,'1:N'); arrow(137,73,127,68,'1:N')
        arrow(68,46,50,36,'1:N'); arrow(87,46,87,33,'1:N'); arrow(113,51,122,26,'1:N')
        for title,(x,y,w,h,body) in boxes.items():
            c.setFillColor(colors.white); c.setStrokeColor(LINE); c.roundRect(x*mm,y*mm,w*mm,h*mm,5,fill=1,stroke=1)
            c.setFillColor(ACCENT if title=='Project' else INK); c.setFont('Helvetica-Bold',8.5); c.drawString((x+3)*mm,(y+h-8)*mm,title)
            c.setFillColor(MUTED); c.setFont('Helvetica',6.8)
            for i,line in enumerate(body.split('\n')): c.drawString((x+3)*mm,(y+h-15-i*8)*mm,line)
        c.setFillColor(MUTED); c.setFont('Helvetica-Oblique',7); c.drawString(0,0,'Arrows point from the one record to its many related records. Full fields are listed on page 2.')

def footer(canvas, doc):
    canvas.saveState(); w,h=A4
    canvas.setStrokeColor(LINE); canvas.line(18*mm,14*mm,w-18*mm,14*mm)
    canvas.setFont('Helvetica',7.5); canvas.setFillColor(MUTED)
    canvas.drawString(18*mm,9*mm,'MUSEUM OF FAILED PROJECTS  /  PROJECT IDEA')
    canvas.drawRightString(w-18*mm,9*mm,f'{doc.page}  /  2')
    canvas.restoreState()

def card(title, text, width):
    t=Table([[Paragraph(title,styles['CellTitle'])],[Paragraph(text,styles['CellX'])]], colWidths=[width])
    t.setStyle(TableStyle([('BACKGROUND',(0,0),(-1,-1),PAPER),('BOX',(0,0),(-1,-1),.6,LINE),('LEFTPADDING',(0,0),(-1,-1),8),('RIGHTPADDING',(0,0),(-1,-1),8),('TOPPADDING',(0,0),(-1,0),7),('BOTTOMPADDING',(0,0),(-1,0),3),('TOPPADDING',(0,1),(-1,1),2),('BOTTOMPADDING',(0,1),(-1,1),7)]))
    return t

doc=SimpleDocTemplate(OUT,pagesize=A4,rightMargin=18*mm,leftMargin=18*mm,topMargin=14*mm,bottomMargin=20*mm,title='Museum of Failed Projects - Project Idea',author='Project proposal')
story=[]
story += [Paragraph('PROJECT IDEA  /  A SKETCH, NOT A CONTRACT',styles['Eyebrow']),Paragraph('Museum of Failed Projects',styles['TitleX']),Paragraph('A place to share what did not work - and help good ideas find a second life.',styles['Sub'])]
story += [Paragraph('A. The idea',styles['H2x']),Paragraph('The Museum of Failed Projects is a community archive for people who have abandoned a creative, technical or entrepreneurial project. Creators publish the story behind the project, what they hoped to achieve, why it stopped and what they learned. Visitors can browse the collection; signed-in members can submit projects, post progress updates and request permission to revive an idea. Administrators curate submissions and manage the community.',styles['BodyX']),Spacer(1,3*mm),Paragraph('B. Data model and relationships',styles['H2x']),ModelMap(),Paragraph('Core relationships',styles['H3x']),Paragraph('A user can submit many projects, write many updates and make many adoption requests. A category groups many projects. Each project can have many images, updates and adoption requests. A project may optionally point to an earlier project that it revives.',styles['BodyX']),PageBreak()]
story += [Paragraph('DATA MODEL  /  FIELDS & PAGES',styles['Eyebrow']),Paragraph('What the app stores and where people go',styles['TitleX']),Paragraph('Every model also has an id and created_at / updated_at timestamps unless stated otherwise.',styles['Sub'])]
models=[
 ('User','name, email (unique), password, email_verified_at (optional), is_admin (boolean).'),
 ('Project','user_id (creator), category_id, parent_project_id (optional), title, description (max 280 chars), story, abandonment_reason, original_goal, what_went_wrong, what_could_be_improved, status, adoption_available (boolean).'),
 ('Category','name (unique), slug (unique), description (optional).'),
 ('ProjectImage','project_id, disk, image_path, caption (optional), sort_order.'),
 ('ProjectUpdate','project_id, user_id (author), content.'),
 ('AdoptionRequest','project_id, user_id (requester), message, status, revived_project_id (optional, unique), responded_at (optional).'),
]
left=[card(a,b,84*mm) for a,b in models[:3]]; right=[card(a,b,84*mm) for a,b in models[3:]]
data=[]
for i in range(3): data.append([left[i],right[i]])
t=Table(data,colWidths=[87*mm,87*mm],hAlign='LEFT')
t.setStyle(TableStyle([('VALIGN',(0,0),(-1,-1),'TOP'),('LEFTPADDING',(0,0),(-1,-1),0),('RIGHTPADDING',(0,0),(-1,-1),3*mm),('TOPPADDING',(0,0),(-1,-1),0),('BOTTOMPADDING',(0,0),(-1,-1),2*mm)]))
story += [t,Spacer(1,2*mm),Paragraph('C. Main pages',styles['H2x'])]
rows=[[Paragraph('PAGE',styles['CellTitle']),Paragraph('WHAT YOU SEE OR DO',styles['CellTitle']),Paragraph('ACCESS',styles['CellTitle'])]]
pages=[
 ('Project gallery','Browse published projects; filter by category or status.','Public'),
 ('Project detail','Read the story, lessons and updates; view images. Request adoption when available.','Public; request needs account'),
 ('Sign in / register','Create an account or access a member account.','Public'),
 ('Submit / edit project','Create a draft or update your own project and images.','Signed-in user'),
 ('My projects & requests','Track submitted projects, updates and adoption request status.','Signed-in user'),
 ('Admin review','Review submissions, publish or return them, manage categories and respond to adoption requests.','Admin'),
]
for a,b,c in pages: rows.append([Paragraph(a,styles['CellX']),Paragraph(b,styles['CellX']),Paragraph(c,styles['CellX'])])
p=Table(rows,colWidths=[36*mm,99*mm,39*mm],repeatRows=1)
p.setStyle(TableStyle([('BACKGROUND',(0,0),(-1,0),LIGHT),('TEXTCOLOR',(0,0),(-1,0),INK),('GRID',(0,0),(-1,-1),.4,LINE),('VALIGN',(0,0),(-1,-1),'TOP'),('LEFTPADDING',(0,0),(-1,-1),6),('RIGHTPADDING',(0,0),(-1,-1),6),('TOPPADDING',(0,0),(-1,-1),5),('BOTTOMPADDING',(0,0),(-1,-1),5)]))
story.append(p)
doc.build(story,onFirstPage=footer,onLaterPages=footer)
print(OUT)
